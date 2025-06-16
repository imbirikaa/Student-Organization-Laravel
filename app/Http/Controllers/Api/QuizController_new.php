<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
  public function index()
  {
    return Quiz::with(['questions.answers', 'event'])->get();
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'event_id' => 'required|exists:events,id',
      'title' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'passing_score' => 'required|integer|min:0|max:100',
      'time_limit' => 'nullable|integer|min:1',
      'required_correct_answers' => 'nullable|integer|min:1',
    ]);

    return Quiz::create($validated);
  }

  public function show($id)
  {
    return Quiz::with(['questions.answers', 'event'])->findOrFail($id);
  }

  public function update(Request $request, $id)
  {
    $quiz = Quiz::findOrFail($id);

    $validated = $request->validate([
      'event_id' => 'sometimes|required|exists:events,id',
      'title' => 'sometimes|nullable|string|max:255',
      'description' => 'sometimes|nullable|string',
      'passing_score' => 'sometimes|required|integer|min:0|max:100',
      'time_limit' => 'sometimes|nullable|integer|min:1',
      'required_correct_answers' => 'sometimes|nullable|integer|min:1',
    ]);

    $quiz->update($validated);
    return $quiz->load(['questions.answers', 'event']);
  }

  public function destroy($id)
  {
    Quiz::destroy($id);
    return response()->noContent();
  }

  /**
   * Get quiz for a specific event
   */
  public function getEventQuiz($eventId): JsonResponse
  {
    try {
      $event = Event::findOrFail($eventId);
      $quiz = Quiz::where('event_id', $eventId)->first();

      if (!$quiz) {
        return response()->json(['message' => 'Bu etkinlik için henüz quiz oluşturulmamış.'], 404);
      }

      // Load quiz with questions and answers
      $quiz->load(['questions.answers']);

      // Transform the data to match frontend expectations
      $transformedQuestions = $quiz->questions->map(function ($question) {
        $options = $question->answers->pluck('answer')->toArray();
        $correctAnswerIndex = $question->answers->search(function ($answer) {
          return $answer->is_correct == 1;
        });

        return [
          'id' => $question->id,
          'question' => $question->question,
          'options' => $options,
          'correct_answer' => $correctAnswerIndex !== false ? $correctAnswerIndex : 0,
          'explanation' => $question->explanation ?? ''
        ];
      });

      $quizData = [
        'id' => $quiz->id,
        'title' => $quiz->title,
        'description' => $quiz->description,
        'time_limit' => $quiz->time_limit,
        'passing_score' => $quiz->passing_score,
        'questions' => $transformedQuestions
      ];

      return response()->json($quizData);
    } catch (\Exception $e) {
      Log::error('Error fetching quiz for event: ' . $e->getMessage());
      return response()->json(['message' => 'Quiz verilerini alırken bir hata oluştu.'], 500);
    }
  }

  /**
   * Create quiz with questions and answers
   */
  public function storeWithQuestions(Request $request): JsonResponse
  {
    try {
      $validated = $request->validate([
        'event_id' => 'required|exists:events,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'passing_score' => 'required|integer|min:0|max:100',
        'time_limit' => 'nullable|integer|min:1',
        'questions' => 'required|array|min:1',
        'questions.*.question' => 'required|string',
        'questions.*.answers' => 'required|array|min:2',
        'questions.*.answers.*.answer' => 'required|string',
        'questions.*.answers.*.is_correct' => 'required|boolean',
      ]);

      // Validate that each question has exactly one correct answer
      foreach ($validated['questions'] as $index => $questionData) {
        $correctCount = collect($questionData['answers'])->where('is_correct', true)->count();
        if ($correctCount !== 1) {
          return response()->json([
            'message' => "Soru " . ($index + 1) . " için tam olarak bir doğru cevap olmalıdır.",
            'errors' => ["questions.{$index}.answers" => ['Her soru için tam olarak bir doğru cevap gereklidir.']]
          ], 422);
        }
      }

      // Check if quiz already exists for this event
      $existingQuiz = Quiz::where('event_id', $validated['event_id'])->first();
      if ($existingQuiz) {
        return response()->json(['message' => 'Bu etkinlik için zaten bir quiz mevcut.'], 409);
      }

      DB::beginTransaction();

      // Create the quiz
      $quiz = Quiz::create([
        'event_id' => $validated['event_id'],
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'passing_score' => $validated['passing_score'],
        'time_limit' => $validated['time_limit'] ?? null,
      ]);

      // Create questions and answers
      foreach ($validated['questions'] as $questionData) {
        $question = $quiz->questions()->create([
          'question' => $questionData['question'],
        ]);

        foreach ($questionData['answers'] as $answerData) {
          $question->answers()->create([
            'answer' => $answerData['answer'],
            'is_correct' => $answerData['is_correct'],
          ]);
        }
      }

      DB::commit();

      // Load the complete quiz with relationships
      $quiz->load(['questions.answers']);

      return response()->json([
        'message' => 'Quiz başarıyla oluşturuldu.',
        'quiz' => $quiz
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error creating quiz: ' . $e->getMessage());
      return response()->json(['message' => 'Quiz oluşturulurken bir hata oluştu.'], 500);
    }
  }

  /**
   * Delete quiz with all related questions and answers
   */
  public function deleteEventQuiz($eventId): JsonResponse
  {
    try {
      $quiz = Quiz::where('event_id', $eventId)->first();

      if (!$quiz) {
        return response()->json(['message' => 'Bu etkinlik için quiz bulunamadı.'], 404);
      }

      DB::beginTransaction();

      // Delete all answers for all questions
      foreach ($quiz->questions as $question) {
        $question->answers()->delete();
      }

      // Delete all questions
      $quiz->questions()->delete();

      // Delete the quiz
      $quiz->delete();

      DB::commit();

      return response()->json(['message' => 'Quiz başarıyla silindi.'], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error deleting quiz: ' . $e->getMessage());
      return response()->json(['message' => 'Quiz silinirken bir hata oluştu.'], 500);
    }
  }
}

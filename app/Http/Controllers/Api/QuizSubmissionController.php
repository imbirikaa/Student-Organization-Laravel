<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizSubmission;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QuizSubmissionController extends Controller
{
    public function index()
    {
        return QuizSubmission::all();
    }
    public function store(Request $request)
    {
        return QuizSubmission::create($request->all());
    }
    public function show($id)
    {
        return QuizSubmission::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $s = QuizSubmission::findOrFail($id);
        $s->update($request->all());
        return $s;
    }
    public function destroy($id)
    {
        QuizSubmission::destroy($id);
        return response()->noContent();
    }

    /**
     * Submit quiz answers and calculate score
     */
    public function submitQuiz(Request $request): JsonResponse
    {
        // Add debugging
        \Log::info('Quiz submission received:', $request->all());

        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array',
            'answers.*' => 'required|integer|min:0', // Answer indices, not IDs
        ]);

        $quiz = Quiz::findOrFail($validated['quiz_id']);
        $user = Auth::user();

        // For testing purposes, use a default user if not authenticated
        if (!$user) {
            $user = (object)['id' => 1]; // Default user for testing
        }

        // Check if user already submitted this quiz
        // For testing purposes, allow multiple submissions by commenting out this check
        $allowMultipleSubmissions = true; // Set to false in production

        if (!$allowMultipleSubmissions) {
            $existingSubmission = QuizSubmission::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->first();

            if ($existingSubmission) {
                return response()->json(['message' => 'Bu quiz için zaten cevap gönderilmiş.'], 400);
            }
        }

        // Get all questions for this quiz
        $questions = $quiz->questions()->with('answers')->get();
        $totalQuestions = $questions->count();
        $correctAnswers = 0;

        // Calculate score based on answer indices
        foreach ($validated['answers'] as $questionIndex => $selectedAnswerIndex) {
            if (isset($questions[$questionIndex])) {
                $question = $questions[$questionIndex];
                $answers = $question->answers;

                // Check if the selected answer index is correct
                if (isset($answers[$selectedAnswerIndex]) && $answers[$selectedAnswerIndex]->is_correct) {
                    $correctAnswers++;
                }
            }
        }

        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passed = $score >= $quiz->passing_score;

        // Create submission
        $submission = QuizSubmission::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'answers' => json_encode($validated['answers']),
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'submission' => $submission,
            'score' => $score,
            'passed' => $passed,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
        ]);
    }

    /**
     * Get user's quiz submission for a specific quiz
     */
    public function getUserSubmission($quizId): JsonResponse
    {
        $user = Auth::user();

        $submission = QuizSubmission::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->first();

        if (!$submission) {
            return response()->json(['message' => 'Bu quiz için henüz cevap gönderilmemiş.'], 404);
        }

        return response()->json($submission);
    }

    /**
     * Get quiz submissions by event ID (for checking if user already took quiz)
     */    public function getSubmissionsByEvent(Request $request): JsonResponse
    {
        $eventId = $request->query('event_id');
        $user = Auth::user();

        // For testing purposes, use a default user if not authenticated
        if (!$user) {
            $user = (object)['id' => 1]; // Default user for testing
        }

        if (!$eventId) {
            return response()->json(['message' => 'Event ID is required'], 400);
        }

        // Get quiz for this event
        $quiz = Quiz::where('event_id', $eventId)->first();

        if (!$quiz) {
            return response()->json(['data' => []], 200);
        }

        // Get user's submissions for this quiz
        $submissions = QuizSubmission::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $submissions]);
    }

    /**
     * Delete all submissions for a specific quiz (for testing purposes)
     */
    public function deleteQuizSubmissions($quizId): JsonResponse
    {
        try {
            $deletedCount = QuizSubmission::where('quiz_id', $quizId)->delete();

            return response()->json([
                'message' => "Quiz ${quizId} için ${deletedCount} submission silindi.",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Submission silme işlemi başarısız.'], 500);
        }
    }

    /**
     * Delete submissions for a specific event (for testing purposes)  
     */
    public function deleteEventSubmissions($eventId): JsonResponse
    {
        try {
            $quiz = Quiz::where('event_id', $eventId)->first();

            if (!$quiz) {
                return response()->json(['message' => 'Bu etkinlik için quiz bulunamadı.'], 404);
            }

            $deletedCount = QuizSubmission::where('quiz_id', $quiz->id)->delete();

            return response()->json([
                'message' => "Event ${eventId} quiz'i için ${deletedCount} submission silindi.",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Submission silme işlemi başarısız.'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminQuizController extends Controller
{
  /**
   * Get all quizzes with comprehensive statistics
   */
  public function index(): JsonResponse
  {
    $quizzes = Quiz::with(['event.community', 'questions.answers'])
      ->withCount(['submissions', 'questions'])
      ->get()
      ->map(function ($quiz) {
        $passedSubmissions = $quiz->submissions()->where('passed', true)->count();
        $averageScore = $quiz->submissions()->avg('score') ?? 0;

        return [
          'id' => $quiz->id,
          'title' => $quiz->title,
          'description' => $quiz->description,
          'event' => [
            'id' => $quiz->event->id,
            'title' => $quiz->event->event,
            'community' => $quiz->event->community->community ?? 'N/A',
            'start_date' => $quiz->event->start_datetime,
            'location' => $quiz->event->location,
          ],
          'settings' => [
            'passing_score' => $quiz->passing_score,
            'time_limit' => $quiz->time_limit,
            'required_correct_answers' => $quiz->required_correct_answers,
          ],
          'statistics' => [
            'total_questions' => $quiz->questions_count,
            'total_submissions' => $quiz->submissions_count,
            'passed_submissions' => $passedSubmissions,
            'pass_rate' => $quiz->submissions_count > 0 ?
              round(($passedSubmissions / $quiz->submissions_count) * 100, 2) : 0,
            'average_score' => round($averageScore, 2),
          ],
          'created_at' => $quiz->created_at,
          'updated_at' => $quiz->updated_at,
        ];
      });

    return response()->json($quizzes);
  }

  /**
   * Get detailed quiz information with questions and submissions
   */
  public function show($id): JsonResponse
  {
    $quiz = Quiz::with([
      'event.community',
      'questions.answers',
      'submissions.user'
    ])->findOrFail($id);

    $submissions = $quiz->submissions->map(function ($submission) {
      return [
        'id' => $submission->id,
        'user' => [
          'id' => $submission->user->id,
          'name' => $submission->user->name,
          'email' => $submission->user->email,
        ],
        'score' => $submission->score,
        'passed' => $submission->passed,
        'answers' => json_decode($submission->answers, true),
        'submitted_at' => $submission->submitted_at,
        'time_taken' => $submission->created_at->diffInMinutes($submission->submitted_at ?? $submission->created_at),
      ];
    });

    $questions = $quiz->questions->map(function ($question) {
      return [
        'id' => $question->id,
        'question' => $question->question,
        'explanation' => $question->explanation,
        'answers' => $question->answers->map(function ($answer) {
          return [
            'id' => $answer->id,
            'answer' => $answer->answer,
            'is_correct' => $answer->is_correct,
          ];
        }),
      ];
    });

    return response()->json([
      'quiz' => [
        'id' => $quiz->id,
        'title' => $quiz->title,
        'description' => $quiz->description,
        'passing_score' => $quiz->passing_score,
        'time_limit' => $quiz->time_limit,
        'required_correct_answers' => $quiz->required_correct_answers,
        'created_at' => $quiz->created_at,
        'updated_at' => $quiz->updated_at,
      ],
      'event' => [
        'id' => $quiz->event->id,
        'title' => $quiz->event->event,
        'description' => $quiz->event->description,
        'community' => $quiz->event->community->community ?? 'N/A',
        'start_date' => $quiz->event->start_datetime,
        'location' => $quiz->event->location,
      ],
      'questions' => $questions,
      'submissions' => $submissions,
      'statistics' => [
        'total_questions' => $questions->count(),
        'total_submissions' => $submissions->count(),
        'passed_submissions' => $submissions->where('passed', true)->count(),
        'average_score' => $submissions->avg('score') ?? 0,
        'score_distribution' => [
          '0-25' => $submissions->where('score', '<=', 25)->count(),
          '26-50' => $submissions->whereBetween('score', [26, 50])->count(),
          '51-75' => $submissions->whereBetween('score', [51, 75])->count(),
          '76-100' => $submissions->where('score', '>', 75)->count(),
        ],
      ],
    ]);
  }

  /**
   * Get quiz analytics dashboard data
   */
  public function analytics(): JsonResponse
  {
    $totalQuizzes = Quiz::count();
    $totalSubmissions = QuizSubmission::count();
    $passedSubmissions = QuizSubmission::where('passed', true)->count();
    $averageScore = QuizSubmission::avg('score') ?? 0;

    // Top performing quizzes
    $topQuizzes = Quiz::with('event')
      ->withCount('submissions')
      ->having('submissions_count', '>', 0)
      ->orderBy('submissions_count', 'desc')
      ->limit(5)
      ->get()
      ->map(function ($quiz) {
        $passRate = $quiz->submissions()->where('passed', true)->count() / $quiz->submissions_count * 100;
        return [
          'id' => $quiz->id,
          'title' => $quiz->title,
          'event_title' => $quiz->event->event,
          'submissions' => $quiz->submissions_count,
          'pass_rate' => round($passRate, 2),
        ];
      });

    // Recent activity
    $recentSubmissions = QuizSubmission::with(['quiz.event', 'user'])
      ->orderBy('created_at', 'desc')
      ->limit(10)
      ->get()
      ->map(function ($submission) {
        return [
          'user_name' => $submission->user->name,
          'quiz_title' => $submission->quiz->title,
          'event_title' => $submission->quiz->event->event,
          'score' => $submission->score,
          'passed' => $submission->passed,
          'submitted_at' => $submission->created_at,
        ];
      });

    // Monthly statistics
    $monthlyStats = DB::table('quiz_submissions')
      ->select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('MONTH(created_at) as month'),
        DB::raw('COUNT(*) as total_submissions'),
        DB::raw('SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_submissions'),
        DB::raw('AVG(score) as average_score')
      )
      ->where('created_at', '>=', now()->subMonths(6))
      ->groupBy('year', 'month')
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc')
      ->get();

    return response()->json([
      'overview' => [
        'total_quizzes' => $totalQuizzes,
        'total_submissions' => $totalSubmissions,
        'passed_submissions' => $passedSubmissions,
        'overall_pass_rate' => $totalSubmissions > 0 ? round(($passedSubmissions / $totalSubmissions) * 100, 2) : 0,
        'average_score' => round($averageScore, 2),
      ],
      'top_quizzes' => $topQuizzes,
      'recent_activity' => $recentSubmissions,
      'monthly_stats' => $monthlyStats,
    ]);
  }

  /**
   * Delete quiz submission (admin only)
   */
  public function deleteSubmission($submissionId): JsonResponse
  {
    try {
      $submission = QuizSubmission::findOrFail($submissionId);
      $submission->delete();

      return response()->json(['message' => 'Submission başarıyla silindi.']);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Submission silinirken hata oluştu.'], 500);
    }
  }

  /**
   * Bulk delete quiz submissions
   */
  public function bulkDeleteSubmissions(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'submission_ids' => 'required|array',
      'submission_ids.*' => 'integer|exists:quiz_submissions,id',
    ]);

    try {
      $deletedCount = QuizSubmission::whereIn('id', $validated['submission_ids'])->delete();

      return response()->json([
        'message' => "{$deletedCount} submission başarıyla silindi.",
        'deleted_count' => $deletedCount,
      ]);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Submissions silinirken hata oluştu.'], 500);
    }
  }

  /**
   * Export quiz data
   */
  public function exportQuizData($quizId): JsonResponse
  {
    $quiz = Quiz::with([
      'event.community',
      'questions.answers',
      'submissions.user'
    ])->findOrFail($quizId);

    $exportData = [
      'quiz_info' => [
        'title' => $quiz->title,
        'description' => $quiz->description,
        'event' => $quiz->event->event,
        'community' => $quiz->event->community->community ?? 'N/A',
        'created_at' => $quiz->created_at->format('d/m/Y H:i'),
      ],
      'questions' => $quiz->questions->map(function ($question, $index) {
        return [
          'question_number' => $index + 1,
          'question' => $question->question,
          'explanation' => $question->explanation,
          'answers' => $question->answers->map(function ($answer, $answerIndex) {
            return [
              'option' => chr(65 + $answerIndex), // A, B, C, D...
              'text' => $answer->answer,
              'correct' => $answer->is_correct ? 'Yes' : 'No',
            ];
          }),
        ];
      }),
      'submissions' => $quiz->submissions->map(function ($submission) {
        return [
          'user_name' => $submission->user->name,
          'user_email' => $submission->user->email,
          'score' => $submission->score,
          'passed' => $submission->passed ? 'Yes' : 'No',
          'submitted_at' => $submission->submitted_at ?
            $submission->submitted_at->format('d/m/Y H:i') :
            $submission->created_at->format('d/m/Y H:i'),
        ];
      }),
    ];

    return response()->json($exportData);
  }
}

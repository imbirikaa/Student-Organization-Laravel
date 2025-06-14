<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\CommunityMembershipController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\ForumCategoryController;
use App\Http\Controllers\Api\ForumTopicController;
use App\Http\Controllers\Api\ForumPostController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizQuestionController;
use App\Http\Controllers\Api\QuizAnswerController;
use App\Http\Controllers\Api\QuizSubmissionController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\ChatRoomUserController;
use App\Http\Controllers\Api\CommunityRoleController;
use App\Http\Controllers\Api\FriendshipController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\SupportTicketMessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SystemLogController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserBadgeController;
use App\Http\Controllers\Api\UserCertificateController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\TestController;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::get('/user/communities', [UserController::class, 'getUserCommunities']);
Route::middleware('auth:sanctum')->get('/user/communities', [UserController::class, 'getUserCommunities']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Enhanced /me endpoint with roles
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // Get user roles - handle multiple cases
    $roles = [];
    try {
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->toArray();
        } elseif (method_exists($user, 'roles')) {
            $roles = $user->roles()->pluck('name')->toArray();
        }
    } catch (\Exception $e) {
        // Fallback: assume user ID 1 is admin
        if ($user->id == 1) {
            $roles = ['admin'];
        } else {
            $roles = ['user'];
        }
    }

    return response()->json([
        'user' => $user,
        'roles' => $roles
    ]);
});

Route::middleware('auth:sanctum')->get('/communities', [CommunityController::class, 'store']);
Route::middleware('auth:sanctum')->post('/communities/{community}/roles', [CommunityRoleController::class, 'store']);


Route::apiResource('badges', BadgeController::class);
Route::apiResource('certificates', CertificateController::class);

Route::apiResource('communities', CommunityController::class);
Route::apiResource('community-memberships', CommunityMembershipController::class);
Route::apiResource('events', EventController::class);
// Get events for a specific community
Route::get('/communities/{community}/events', [EventController::class, 'showByCommunity']);

// Event registration routes
Route::get('/events/{event}/check-registration', [EventController::class, 'checkRegistration']);
Route::get('/events/{event}/registrations', [EventController::class, 'getRegistrations']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events/{event}/register', [EventController::class, 'register']);
    Route::delete('/events/{event}/unregister', [EventController::class, 'unregister']);
});

Route::apiResource('users', UserController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('universities', UniversityController::class);
Route::apiResource('forum-categories', ForumCategoryController::class);
Route::apiResource('forum-topics', ForumTopicController::class);
Route::apiResource('forum-posts', ForumPostController::class);
Route::apiResource('quizzes', QuizController::class);
Route::apiResource('quiz-questions', QuizQuestionController::class);
Route::apiResource('quiz-answers', QuizAnswerController::class);
Route::apiResource('quiz-submissions', QuizSubmissionController::class);
Route::apiResource('chat-rooms', ChatRoomController::class);
Route::apiResource('chat-room-users', ChatRoomUserController::class);
Route::apiResource('messages', MessageController::class);
Route::apiResource('support-tickets', SupportTicketController::class);
Route::apiResource('support-ticket-messages', SupportTicketMessageController::class);
Route::apiResource('notifications', NotificationController::class);
Route::apiResource('system-logs', SystemLogController::class);
Route::apiResource('user-roles', UserRoleController::class);
Route::apiResource('user-badges', UserBadgeController::class);
Route::apiResource('friendships', FriendshipController::class);
Route::apiResource('user-certificates', UserCertificateController::class);
Route::middleware('auth:sanctum')->post('/communities/{community}/apply', [CommunityMembershipController::class, 'apply']);

// Admin routes
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'getStats']);
    Route::get('/recent-users', [AdminController::class, 'getRecentUsers']);
    Route::get('/recent-communities', [AdminController::class, 'getRecentCommunities']);
    Route::get('/pending-applications', [AdminController::class, 'getPendingApplications']);

    // Community application management
    Route::get('/community-applications', [CommunityMembershipController::class, 'getPendingApplications']);
    Route::get('/communities/{community}/applications', [CommunityMembershipController::class, 'getCommunityApplications']);
    Route::post('/applications/{membership}/approve', [CommunityMembershipController::class, 'approveApplication']);
    Route::post('/applications/{membership}/reject', [CommunityMembershipController::class, 'rejectApplication']);
});

// Test routes (for debugging)
Route::get('/test/admin-stats', [TestController::class, 'testAdminStats']);
Route::get('/test/auth', [TestController::class, 'testAuth']);

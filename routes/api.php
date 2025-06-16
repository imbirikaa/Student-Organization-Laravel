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
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizQuestionController;
use App\Http\Controllers\Api\QuizAnswerController;
use App\HttpControllers\Api\QuizSubmissionController;
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
use App\Http\Controllers\Api\QuizSubmissionController as ApiQuizSubmissionController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\FileUploadController;

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
Route::middleware('auth:sanctum')->get('/user/permissions', [UserController::class, 'getUserPermissions']);
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

Route::middleware('auth:sanctum')->get('/communities', [CommunityController::class, 'index']);
Route::middleware('auth:sanctum')->post('/communities/{community}/roles', [CommunityRoleController::class, 'store']);


Route::apiResource('badges', BadgeController::class);
Route::apiResource('certificates', CertificateController::class);

Route::apiResource('communities', CommunityController::class);
Route::apiResource('community-memberships', CommunityMembershipController::class);

// Community-specific routes with permission protection
Route::middleware(['auth:sanctum'])->group(function () {
    // Event management within communities
    Route::middleware(['community.permission:create_events,community'])->post('/communities/{community}/events', [EventController::class, 'store']);
    Route::middleware(['community.permission:edit_events,community'])->put('/communities/{community}/events/{event}', [EventController::class, 'update']);
    Route::middleware(['community.permission:delete_events,community'])->delete('/communities/{community}/events/{event}', [EventController::class, 'destroy']);

    // Member management
    Route::middleware(['community.permission:view_members,community'])->get('/communities/{community}/members', [CommunityMembershipController::class, 'getCommunityMembers']);
    Route::middleware(['community.permission:remove_members,community'])->delete('/communities/{community}/members/{membership}', [CommunityMembershipController::class, 'removeMember']);
    Route::middleware(['community.permission:assign_roles,community'])->patch('/communities/{community}/members/{membership}/role', [CommunityMembershipController::class, 'assignRole']);

    // Direct permission management
    Route::middleware(['community.permission:assign_roles,community'])->post('/communities/{community}/members/{membership}/permissions', [CommunityMembershipController::class, 'assignPermissions']);
    Route::middleware(['community.permission:assign_roles,community'])->delete('/communities/{community}/members/{membership}/permissions', [CommunityMembershipController::class, 'removePermissions']);
    Route::middleware(['community.permission:view_members,community'])->get('/communities/{community}/members/{membership}/direct-permissions', [CommunityMembershipController::class, 'getUserDirectPermissions']);

    // Community applications
    Route::middleware(['community.permission:view_members,community'])->get('/communities/{community}/applications', [CommunityMembershipController::class, 'getCommunityApplications']);
    Route::middleware(['community.permission:approve_members,community'])->post('/communities/{community}/applications/{membership}/approve', [CommunityMembershipController::class, 'approveApplication']);
    Route::middleware(['community.permission:reject_members,community'])->post('/communities/{community}/applications/{membership}/reject', [CommunityMembershipController::class, 'rejectApplication']);

    // Forum management
    Route::middleware(['community.permission:moderate_content,community'])->post('/communities/{community}/forum', [ForumTopicController::class, 'store']);
    Route::middleware(['community.permission:moderate_content,community'])->put('/communities/{community}/forum/{topic}', [ForumTopicController::class, 'update']);
    Route::middleware(['community.permission:moderate_content,community'])->delete('/communities/{community}/forum/{topic}', [ForumTopicController::class, 'destroy']);

    // Audit logs
    Route::middleware(['community.permission:view_audit_logs,community'])->get('/communities/{community}/audit-logs', [AuditController::class, 'getCommunityAuditLogs']);
    Route::middleware(['community.permission:view_audit_logs,community'])->get('/communities/{community}/audit-actions', [AuditController::class, 'getAuditActions']);
    Route::middleware(['community.permission:view_audit_logs,community'])->get('/communities/{community}/audit-stats', [AuditController::class, 'getAuditStats']);
});

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

// User's own registrations and attendance codes
Route::middleware('auth:sanctum')->get('/my-attendance-codes', [EventController::class, 'getMyAttendanceCodes']);

Route::apiResource('users', UserController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('universities', UniversityController::class);
Route::apiResource('forum-categories', ForumCategoryController::class);
Route::apiResource('forum-topics', ForumTopicController::class);
Route::apiResource('forum-posts', ForumPostController::class);
Route::apiResource('quizzes', QuizController::class);
Route::apiResource('quiz-questions', QuizQuestionController::class);
Route::apiResource('quiz-answers', QuizAnswerController::class);
Route::apiResource('quiz-submissions', ApiQuizSubmissionController::class);
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

    // Admin data management
    Route::get('/events', [AdminController::class, 'getEvents']);
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/communities', [AdminController::class, 'getCommunities']);

    // Attendance check-in routes
    Route::get('/events/{eventId}/check-in-stats', [AdminController::class, 'getEventCheckInStats']);
    Route::post('/events/{eventId}/check-in-by-code', [AdminController::class, 'checkInByCode']);
    Route::post('/events/{eventId}/bulk-check-in', [AdminController::class, 'bulkCheckIn']);
});

// File upload routes
Route::middleware('auth:sanctum')->prefix('files')->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::get('/{uploadableType}/{uploadableId}', [FileUploadController::class, 'getFiles']);
    Route::get('/download/{id}', [FileUploadController::class, 'downloadFile'])->name('api.files.download');
    Route::delete('/{id}', [FileUploadController::class, 'deleteFile']);
});

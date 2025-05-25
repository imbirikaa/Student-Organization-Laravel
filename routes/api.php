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
use App\Http\Controllers\Api\FriendshipController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\SupportTicketMessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SystemLogController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserBadgeController;
use App\Http\Controllers\Api\UserCertificateController;
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

Route::apiResource('badges', BadgeController::class);
Route::apiResource('certificates', CertificateController::class);
Route::apiResource('communities', CommunityController::class);
Route::apiResource('community-memberships', CommunityMembershipController::class);
Route::apiResource('events', EventController::class);
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

<?php

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
use Illuminate\Http\Request;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

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
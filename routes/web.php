<?php

use App\Http\Controllers\Api\UserController;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});

Route::get('/login', function () {
    return view('login');
})->name('login.submit');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // ✅ This will now work
        // $token = $user->createToken('auth_Ztoken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            // 'access_token' => $token,
            // 'token_type' => 'Bearer',
            // 'user' => $user,
        ]);
    }

    return response()->json([
        'message' => 'The provided credentials are incorrect.'
    ], 401);
});


Route::middleware('auth:sanctum')->get('/api/user', function (Request $request) {
    $user = $request->user();
    $user->community_count = 55;
    $user->topic_count = 55;
    $user->friend_count =  $user->allAcceptedFriendships()->count(); // if implemented
    $user->event_count = 55; // if exists
    return $user;
});



Route::get('/dashboard', function () {
    return 'You are logged in!';
})->middleware('auth');

Route::get('/check', function () {
    return auth()->check() ? auth()->user() : 'Not authenticated';
});

Route::post('/logout', function (Request $request) {
    Auth::logout(); // Log out the user

    $request->session()->invalidate();   // Invalidate the session
    $request->session()->regenerateToken(); // Regenerate CSRF token

    return response()->json(['message' => 'Logged out successfully']);
});

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

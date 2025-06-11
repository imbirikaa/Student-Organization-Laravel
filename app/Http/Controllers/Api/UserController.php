<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Friendship;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::paginate(10);
    }


    public function store(RegisterUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json($user, 201);
    }

    public function show($nickname)
    {
        $user = User::where('nickname', $nickname)->firstOrFail();

        $loggedInUserId = auth()->id(); // or request()->user()->id;



        $isFriend = Friendship::where(function ($query) use ($loggedInUserId, $user) {
            $query->where('user_id', $loggedInUserId)
                ->where('friend_user_id', $user->id)
                ->where('status', 'accepted');
        })->orWhere(function ($query) use ($loggedInUserId, $user) {
            $query->where('user_id', $user->id)
                ->where('friend_user_id', $loggedInUserId)
                ->where('status', 'accepted');
        })->exists();

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'about' => $user->about,
            'profile_picture' => $user->profile_picture,
            'friend_count' => $user->friendships()->count(),
            'community_count' => $user->communities()->count(),
            'event_count' => $user->events()->count(),
            'is_friend' => $isFriend,
        ]);
    }


    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email' . $user->id,
            'university_id'  => 'required|exists:universities,id',
            'department_id'  => 'required|exists:departments,id',
            'profile_image'  => 'nullable|image|max:2048', // optional file
        ]);

        $user->update($request->all());
        return $user;
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->noContent();
    }

    public function userCommunityCount(User $user)
    {
        // $user = User::findOrFail($id);
        $count = $user->communities()->count();

        return response()->json([
            // 'user_id' => $id,
            'community_count' => $count,
        ]);
    }
}

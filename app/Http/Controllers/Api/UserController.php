<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Friendship;
use Illuminate\Support\Facades\Hash;
use App\Models\CommunityMembership;
use App\Models\CommunityRolePermission;

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

        $loggedInUserId = request()->user()->id;



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

    public function getUserCommunities(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Fetch the communities the user is related to
        // (Assuming you have a 'communities' relationship on your User model)
        $communities = $user->communities()->get(['communities.id', 'communities.community']);


        return response()->json($communities);
    }

    /**
     * Get user's permissions across all communities
     */
    public function getUserPermissions(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $memberships = CommunityMembership::with(['community', 'role'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        $permissions = [];

        foreach ($memberships as $membership) {
            if ($membership->role) {
                $rolePermissions = CommunityRolePermission::where('community_role_id', $membership->role->id)
                    ->with('permission')
                    ->get()
                    ->pluck('permission.name')
                    ->toArray();

                $permissions[] = [
                    'communityId' => $membership->community_id,
                    'community' => [
                        'id' => $membership->community->id,
                        'name' => $membership->community->community,
                    ],
                    'role' => [
                        'id' => $membership->role->id,
                        'role' => $membership->role->role,
                        'description' => $membership->role->description,
                    ],
                    'permissions' => $rolePermissions
                ];
            }
        }

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }
}

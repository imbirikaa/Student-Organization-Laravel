<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use App\Models\CommunityMembership;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CommunityApplicationReceived;

class CommunityMembershipController extends Controller
{
    /**
     * Handle a user's application to join a community.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Community  $community
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request, Community $community)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- CORRECTED LOGIC ---
        // 1. First, check if a membership record already exists.
        $existingMembership = CommunityMembership::where('user_id', $user->id)
            ->where('community_id', $community->id)
            ->first();

        if ($existingMembership) {
            // 2. If a record exists, return a 409 Conflict error immediately.
            return response()->json(['message' => 'You have already applied to this community.'], 409);
        }

        // 3. If no record exists, create the new application.
        // Assuming 'role_id' = 2 is the default "member" role.
        $membership = CommunityMembership::create([
            'user_id' => $user->id,
            'community_id' => $community->id,
            'role_id' => 2,
            'status' => 'pending',
        ]);

        // 4. Find the creator and send the notification.
        $creator = $community->creator;
        if ($creator) {
            $creator->notify(new CommunityApplicationReceived($user, $community));
        }

        // 5. Return a success response.
        return response()->json([
            'message' => 'Your application has been submitted successfully.',
            'membership' => $membership,
        ], 201);
    }

    public function index()
    {
        return CommunityMembership::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'community_id' => 'required|exists:communities,id',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:pending,approved,rejected',
        ]);
        return CommunityMembership::create($validatedData);
    }

    // EDITED: Using Route-Model Binding
    public function show(CommunityMembership $communityMembership)
    {
        return $communityMembership;
    }

    // EDITED: Using Route-Model Binding
    public function update(Request $request, CommunityMembership $communityMembership)
    {
        $validatedData = $request->validate([
            'role_id' => 'sometimes|exists:roles,id',
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        $communityMembership->update($validatedData);
        return $communityMembership;
    }

    // EDITED: Using Route-Model Binding
    public function destroy(CommunityMembership $communityMembership)
    {
        $communityMembership->delete();
        return response()->noContent();
    }
}
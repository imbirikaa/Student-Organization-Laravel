<?php

namespace App\Http\Controllers\Api;

use App\Models\Community;
use App\Models\CommunityMembership;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CommunityApplicationReceived;
use App\Notifications\CommunityApplicationApproved;
use App\Notifications\CommunityApplicationRejected;

class CommunityMembershipController extends Controller
{
    /**
     * Check if user has admin role
     */
    private function isAdmin($user)
    {
        try {
            return method_exists($user, 'hasRole') && $user->hasRole('admin');
        } catch (\Exception $e) {
            return ($user->id == 1);
        }
    }
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

    /**
     * Approve a community membership application.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommunityMembership  $communityMembership
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, CommunityMembership $communityMembership)
    {
        // 1. Update the membership status to 'approved'.
        $communityMembership->update(['status' => 'approved']);

        // 2. Notify the user about the approval.
        $user = $communityMembership->user;
        $community = $communityMembership->community;
        $user->notify(new CommunityApplicationApproved($user, $community));

        // 3. Return a success response.
        return response()->json([
            'message' => 'The membership application has been approved.',
            'membership' => $communityMembership,
        ]);
    }

    /**
     * Reject a community membership application.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommunityMembership  $communityMembership
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, CommunityMembership $communityMembership)
    {
        // 1. Update the membership status to 'rejected'.
        $communityMembership->update(['status' => 'rejected']);

        // 2. Notify the user about the rejection.
        $user = $communityMembership->user;
        $community = $communityMembership->community;
        $user->notify(new CommunityApplicationRejected($user, $community));

        // 3. Return a success response.
        return response()->json([
            'message' => 'The membership application has been rejected.',
            'membership' => $communityMembership,
        ]);
    }

    /**
     * Get all pending applications for admin review
     */
    public function getPendingApplications()
    {
        $applications = CommunityMembership::with(['user', 'community'])
            ->where('status', 'pending')
            ->orderBy('application_date', 'asc')
            ->get()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'user' => [
                        'id' => $membership->user->id,
                        'name' => $membership->user->first_name . ' ' . $membership->user->last_name,
                        'email' => $membership->user->email,
                    ],
                    'community' => [
                        'id' => $membership->community->id,
                        'name' => $membership->community->community,
                        'logo' => $membership->community->logo,
                    ],
                    'application_date' => $membership->application_date,
                    'status' => $membership->status,
                ];
            });

        return response()->json([
            'total_pending' => $applications->count(),
            'applications' => $applications
        ]);
    }

    /**
     * Get applications for a specific community (for community admins)
     */
    public function getCommunityApplications(Community $community)
    {
        $user = Auth::user();

        // Check if user is admin or community owner        
        if (!$this->isAdmin($user) && $community->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $applications = CommunityMembership::with(['user'])
            ->where('community_id', $community->id)
            ->where('status', 'pending')
            ->orderBy('application_date', 'asc')
            ->get()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'user' => [
                        'id' => $membership->user->id,
                        'name' => $membership->user->first_name . ' ' . $membership->user->last_name,
                        'email' => $membership->user->email,
                    ],
                    'application_date' => $membership->application_date,
                    'status' => $membership->status,
                ];
            });

        return response()->json([
            'community' => $community->community,
            'total_pending' => $applications->count(),
            'applications' => $applications
        ]);
    }

    /**
     * Approve a community application
     */
    public function approveApplication(Request $request, CommunityMembership $membership)
    {
        $user = Auth::user();

        // Check if user has permission to approve (admin or community owner)
        if (!$this->isAdmin($user) && $membership->community->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($membership->status !== 'pending') {
            return response()->json(['message' => 'Application has already been processed'], 400);
        }

        $membership->update([
            'status' => 'approved',
            'approval_date' => now(),
        ]);

        // Send notification to applicant
        $membership->user->notify(new CommunityApplicationApproved($membership->community));

        return response()->json([
            'message' => 'Application approved successfully',
            'membership' => $membership->load(['user', 'community'])
        ]);
    }

    /**
     * Reject a community application
     */
    public function rejectApplication(Request $request, CommunityMembership $membership)
    {
        $user = Auth::user();

        // Check if user has permission to reject (admin or community owner)
        if (!$this->isAdmin($user) && $membership->community->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($membership->status !== 'pending') {
            return response()->json(['message' => 'Application has already been processed'], 400);
        }

        $reason = $request->input('reason', 'Application rejected');

        $membership->update([
            'status' => 'rejected',
            'approval_date' => now(),
        ]);

        // Send notification to applicant with reason
        $membership->user->notify(new CommunityApplicationRejected($membership->community, $reason));

        return response()->json([
            'message' => 'Application rejected successfully',
            'membership' => $membership->load(['user', 'community'])
        ]);
    }
}

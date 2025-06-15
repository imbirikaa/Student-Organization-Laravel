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
use App\Traits\HasCommunityPermissions;
use App\Traits\LogsAuditTrail;

class CommunityMembershipController extends Controller
{
    use HasCommunityPermissions, LogsAuditTrail;
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
        $user = request()->user();

        // Check if user has permission to view members in this community
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'view_members');
        if ($permissionError) {
            return $permissionError;
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
        $user = $request->user();

        // Check if user has permission to approve members in this community
        $permissionError = $this->requireCommunityPermission($user, $membership->community_id, 'approve_members');
        if ($permissionError) {
            $this->logPermissionAction(
                'member_approval',
                $membership->community_id,
                'approve_members',
                false,
                ['target_user_id' => $membership->user_id],
                $request
            );
            return $permissionError;
        }

        if ($membership->status !== 'pending') {
            return response()->json(['message' => 'Application has already been processed'], 400);
        }

        $oldStatus = $membership->status;
        $membership->update([
            'status' => 'approved',
            'approval_date' => now(),
        ]);

        // Log the approval action
        $this->logAudit(
            'member_approved',
            $membership->community_id,
            'CommunityMembership',
            $membership->id,
            ['status' => $oldStatus],
            ['status' => 'approved', 'approval_date' => now()->toISOString()],
            "Approved membership application for {$membership->user->email}",
            $request
        );

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
        $user = $request->user();

        // Check if user has permission to reject members in this community
        $permissionError = $this->requireCommunityPermission($user, $membership->community_id, 'reject_members');
        if ($permissionError) {
            $this->logPermissionAction(
                'member_rejection',
                $membership->community_id,
                'reject_members',
                false,
                ['target_user_id' => $membership->user_id],
                $request
            );
            return $permissionError;
        }

        if ($membership->status !== 'pending') {
            return response()->json(['message' => 'Application has already been processed'], 400);
        }

        $reason = $request->input('reason', 'Application rejected');
        $oldStatus = $membership->status;

        $membership->update([
            'status' => 'rejected',
            'approval_date' => now(),
        ]);

        // Log the rejection action
        $this->logAudit(
            'member_rejected',
            $membership->community_id,
            'CommunityMembership',
            $membership->id,
            ['status' => $oldStatus],
            ['status' => 'rejected', 'reason' => $reason, 'approval_date' => now()->toISOString()],
            "Rejected membership application for {$membership->user->email}" . ($reason ? " - Reason: {$reason}" : ""),
            $request
        );

        // Send notification to applicant with reason
        $membership->user->notify(new CommunityApplicationRejected($membership->community, $reason));

        return response()->json([
            'message' => 'Application rejected successfully',
            'membership' => $membership->load(['user', 'community'])
        ]);
    }

    /**
     * Get community members (for community admins)
     */
    public function getCommunityMembers(Community $community)
    {
        $user = request()->user();

        // Permission is already checked by middleware, but we can add additional checks here
        $members = CommunityMembership::with(['user', 'role'])
            ->where('community_id', $community->id)
            ->where('status', 'approved')
            ->orderBy('approval_date', 'desc')
            ->get()
            ->map(function ($membership) {
                // Get role permissions
                $rolePermissions = $membership->role ?
                    $membership->role->permissions()->pluck('name')->toArray() : [];

                // Get custom permissions
                $customPermissions = $membership->custom_permissions ?? [];

                // Combine all permissions
                $allPermissions = array_unique(array_merge($rolePermissions, $customPermissions));

                return [
                    'id' => $membership->id,
                    'user' => [
                        'id' => $membership->user->id,
                        'first_name' => $membership->user->first_name,
                        'last_name' => $membership->user->last_name,
                        'email' => $membership->user->email,
                    ],
                    'community_id' => $membership->community_id,
                    'role' => [
                        'id' => $membership->role?->id,
                        'role' => $membership->role?->role,
                        'description' => $membership->role?->description,
                    ],
                    'status' => $membership->status,
                    'permissions' => $allPermissions,
                    'custom_permissions' => $customPermissions,
                    'joined_date' => $membership->approval_date,
                ];
            });

        return response()->json([
            'community' => $community->community,
            'total_members' => $members->count(),
            'members' => $members
        ]);
    }

    /**
     * Remove a member from the community
     */
    public function removeMember(Community $community, CommunityMembership $membership)
    {
        $user = request()->user();

        // Verify membership belongs to the community
        if ($membership->community_id !== $community->id) {
            return response()->json(['message' => 'Member not found in this community'], 404);
        }

        // Don't allow removing founders unless you're also a founder
        if ($membership->role && $membership->role->role === 'Kurucu') {
            $userRole = $this->getUserRoleInCommunity($user, $community->id);
            if (!$userRole || $userRole->role !== 'Kurucu') {
                return response()->json(['message' => 'Cannot remove founder unless you are also a founder'], 403);
            }
        }

        $memberData = [
            'user_id' => $membership->user_id,
            'email' => $membership->user->email,
            'name' => $membership->user->first_name . ' ' . $membership->user->last_name,
            'role' => $membership->role ? $membership->role->role : 'No role',
        ];

        // Log the removal action before deletion
        $this->logMemberRemoval(
            $community->id,
            $membership->user_id,
            $membership->user->email,
            request()->input('reason'),
            request()
        );

        $membership->delete();

        return response()->json([
            'message' => 'Member removed successfully',
            'removed_member' => [
                'name' => $memberData['name'],
                'email' => $memberData['email'],
            ]
        ]);
    }

    /**
     * Assign a role to a community member
     */
    public function assignRole(Request $request, Community $community, CommunityMembership $membership)
    {
        $user = $request->user();

        $request->validate([
            'role_id' => 'required|exists:community_roles,id'
        ]);

        // Verify membership belongs to the community
        if ($membership->community_id !== $community->id) {
            return response()->json(['message' => 'Member not found in this community'], 404);
        }

        // Additional business logic: Only founders can assign founder role
        $newRole = \App\Models\CommunityRole::find($request->role_id);
        $oldRole = $membership->role;

        if ($newRole->role === 'Kurucu') {
            $userRole = $this->getUserRoleInCommunity($user, $community->id);
            if (!$userRole || $userRole->role !== 'Kurucu') {
                return response()->json(['message' => 'Only founders can assign founder role'], 403);
            }
        }

        $membership->update([
            'community_role_id' => $request->role_id
        ]);

        // Log the role assignment
        $this->logRoleAssignment(
            $community->id,
            $membership->user_id,
            $oldRole ? $oldRole->role : 'No role',
            $newRole->role,
            $request
        );

        return response()->json([
            'message' => 'Role assigned successfully',
            'member' => [
                'name' => $membership->user->first_name . ' ' . $membership->user->last_name,
                'email' => $membership->user->email,
                'new_role' => $newRole->role,
            ]
        ]);
    }

    /**
     * Assign specific permissions to a community member
     */
    public function assignPermissions(Request $request, Community $community, CommunityMembership $membership)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user has permission to assign roles (which includes permission management)
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'assign_roles');
        if ($permissionError) {
            return $permissionError;
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string'
        ]);

        try {
            $targetUser = $membership->user;
            $permissions = $request->permissions;

            // For now, we'll store custom permissions in the community_memberships table
            // as a JSON field

            // Get current permissions from membership
            $currentPermissions = $membership->custom_permissions ?? [];

            // Merge with new permissions (avoiding duplicates)
            $allPermissions = array_unique(array_merge($currentPermissions, $permissions));

            // Update membership with new permissions
            $membership->update([
                'custom_permissions' => $allPermissions
            ]);

            // Log the permission assignment
            $this->logAudit(
                'permissions_assigned',
                $community->id,
                'user_permissions',
                $targetUser->id,
                [],
                $permissions,
                "Assigned permissions to {$targetUser->first_name} {$targetUser->last_name}: " . implode(', ', $permissions),
                $request
            );

            return response()->json([
                'message' => 'Permissions assigned successfully',
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->first_name . ' ' . $targetUser->last_name,
                    'permissions' => $allPermissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove specific permissions from a community member
     */
    public function removePermissions(Request $request, Community $community, CommunityMembership $membership)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user has permission to assign roles (which includes permission management)
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'assign_roles');
        if ($permissionError) {
            return $permissionError;
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string'
        ]);

        try {
            $targetUser = $membership->user;
            $permissions = $request->permissions;

            // Get current permissions from membership
            $currentPermissions = $membership->custom_permissions ?? [];

            // Remove specified permissions
            $remainingPermissions = array_diff($currentPermissions, $permissions);

            // Update membership
            $membership->update([
                'custom_permissions' => array_values($remainingPermissions)
            ]);

            // Log the permission removal
            $this->logAudit(
                'permissions_removed',
                $community->id,
                'user_permissions',
                $targetUser->id,
                $permissions,
                [],
                "Removed permissions from {$targetUser->first_name} {$targetUser->last_name}: " . implode(', ', $permissions),
                $request
            );

            return response()->json([
                'message' => 'Permissions removed successfully',
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->first_name . ' ' . $targetUser->last_name,
                    'removed_permissions' => $permissions,
                    'remaining_permissions' => array_values($remainingPermissions)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's direct permissions (not from roles) for a community
     */
    public function getUserDirectPermissions(Community $community, CommunityMembership $membership)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user has permission to view members
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'view_members');
        if ($permissionError) {
            return $permissionError;
        }

        try {
            $targetUser = $membership->user;

            // Get direct permissions from membership
            $directPermissions = $membership->custom_permissions ?? [];

            return response()->json([
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->first_name . ' ' . $targetUser->last_name,
                    'direct_permissions' => $directPermissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get user permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

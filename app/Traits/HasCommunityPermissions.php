<?php

namespace App\Traits;

use App\Models\CommunityMembership;
use App\Models\CommunityRolePermission;
use Illuminate\Http\JsonResponse;

trait HasCommunityPermissions
{
    /**
     * Check if user has a specific permission in a community
     */
    public function hasPermissionInCommunity($user, $communityId, $permission): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user is a member of the community
        $membership = CommunityMembership::where('user_id', $user->id)
            ->where('community_id', $communityId)
            ->where('status', 'approved')
            ->with('role')
            ->first();

        if (!$membership || !$membership->role) {
            return false;
        }        // First check if user has the permission through custom permissions
        if ($membership->custom_permissions && is_array($membership->custom_permissions)) {
            if (in_array($permission, $membership->custom_permissions)) {
                return true;
            }
        }

        // Then check if the role has the required permission
        $hasPermission = CommunityRolePermission::where('community_role_id', $membership->role->id)
            ->whereHas('permission', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();

        return $hasPermission;
    }

    /**
     * Get user's role in a specific community
     */
    public function getUserRoleInCommunity($user, $communityId)
    {
        if (!$user) {
            return null;
        }

        $membership = CommunityMembership::where('user_id', $user->id)
            ->where('community_id', $communityId)
            ->where('status', 'approved')
            ->with('role')
            ->first();

        return $membership ? $membership->role : null;
    }

    /**
     * Get user's permissions in a specific community
     */
    public function getUserPermissionsInCommunity($user, $communityId): array
    {
        if (!$user) {
            return [];
        }

        $membership = CommunityMembership::where('user_id', $user->id)
            ->where('community_id', $communityId)
            ->where('status', 'approved')
            ->with('role')
            ->first();

        if (!$membership || !$membership->role) {
            return [];
        }

        return CommunityRolePermission::where('community_role_id', $membership->role->id)
            ->with('permission')
            ->get()
            ->pluck('permission.name')
            ->toArray();
    }

    /**
     * Check if user is a member of the community
     */
    public function isCommunityMember($user, $communityId): bool
    {
        if (!$user) {
            return false;
        }

        return CommunityMembership::where('user_id', $user->id)
            ->where('community_id', $communityId)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Require permission and return error response if not authorized
     */
    public function requireCommunityPermission($user, $communityId, $permission): ?JsonResponse
    {
        if (!$this->hasPermissionInCommunity($user, $communityId, $permission)) {
            $roleName = $this->getUserRoleInCommunity($user, $communityId)?->role ?? 'None';

            return response()->json([
                'message' => "Access denied. Required permission: {$permission}",
                'your_role' => $roleName,
                'required_permission' => $permission
            ], 403);
        }

        return null;
    }

    /**
     * Check if user has any management role in community (Kurucu, Yönetici, Moderatör)
     */
    public function hasManagementRole($user, $communityId): bool
    {
        $role = $this->getUserRoleInCommunity($user, $communityId);

        if (!$role) {
            return false;
        }

        return in_array($role->role, ['Kurucu', 'Yönetici', 'Moderatör']);
    }

    /**
     * Check if user is community founder
     */
    public function isCommunityFounder($user, $communityId): bool
    {
        $role = $this->getUserRoleInCommunity($user, $communityId);
        return $role && $role->role === 'Kurucu';
    }

    /**
     * Check if user is community admin or founder
     */
    public function isCommunityAdmin($user, $communityId): bool
    {
        $role = $this->getUserRoleInCommunity($user, $communityId);
        return $role && in_array($role->role, ['Kurucu', 'Yönetici']);
    }
}

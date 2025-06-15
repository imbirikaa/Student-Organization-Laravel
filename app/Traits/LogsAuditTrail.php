<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogsAuditTrail
{
  /**
   * Log an action to the audit trail
   */
  protected function logAudit(
    string $action,
    ?int $communityId = null,
    ?string $resourceType = null,
    ?int $resourceId = null,
    ?array $oldValues = null,
    ?array $newValues = null,
    ?string $description = null,
    ?Request $request = null
  ): AuditLog {
    $request = $request ?: request();

    return AuditLog::create([
      'user_id' => Auth::id(),
      'community_id' => $communityId,
      'action' => $action,
      'resource_type' => $resourceType,
      'resource_id' => $resourceId,
      'old_values' => $oldValues,
      'new_values' => $newValues,
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
      'description' => $description ?: $this->getDefaultDescription($action, $resourceType, $resourceId),
    ]);
  }

  /**
   * Log role assignment action
   */
  protected function logRoleAssignment(
    int $communityId,
    int $userId,
    string $oldRole,
    string $newRole,
    ?Request $request = null
  ): AuditLog {
    return $this->logAudit(
      action: 'role_assigned',
      communityId: $communityId,
      resourceType: 'User',
      resourceId: $userId,
      oldValues: ['role' => $oldRole],
      newValues: ['role' => $newRole],
      description: "Role changed from '{$oldRole}' to '{$newRole}' for user ID {$userId}",
      request: $request
    );
  }

  /**
   * Log member removal action
   */
  protected function logMemberRemoval(
    int $communityId,
    int $userId,
    string $userEmail,
    string $reason = null,
    ?Request $request = null
  ): AuditLog {
    return $this->logAudit(
      action: 'member_removed',
      communityId: $communityId,
      resourceType: 'User',
      resourceId: $userId,
      newValues: ['reason' => $reason],
      description: "Member {$userEmail} (ID: {$userId}) removed from community" . ($reason ? " - Reason: {$reason}" : ""),
      request: $request
    );
  }

  /**
   * Log permission-sensitive action
   */
  protected function logPermissionAction(
    string $action,
    int $communityId,
    string $permission,
    bool $granted = true,
    ?array $additionalData = null,
    ?Request $request = null
  ): AuditLog {
    return $this->logAudit(
      action: $granted ? $action : 'permission_denied',
      communityId: $communityId,
      oldValues: ['permission_required' => $permission],
      newValues: array_merge(['permission_granted' => $granted], $additionalData ?: []),
      description: $granted
        ? "Permission '{$permission}' granted for action '{$action}'"
        : "Permission '{$permission}' denied for action '{$action}'",
      request: $request
    );
  }

  /**
   * Log community management actions
   */
  protected function logCommunityAction(
    string $action,
    int $communityId,
    ?array $oldValues = null,
    ?array $newValues = null,
    ?string $description = null,
    ?Request $request = null
  ): AuditLog {
    return $this->logAudit(
      action: $action,
      communityId: $communityId,
      resourceType: 'Community',
      resourceId: $communityId,
      oldValues: $oldValues,
      newValues: $newValues,
      description: $description,
      request: $request
    );
  }

  /**
   * Log event management actions
   */
  protected function logEventAction(
    string $action,
    int $communityId,
    int $eventId,
    ?array $oldValues = null,
    ?array $newValues = null,
    ?string $description = null,
    ?Request $request = null
  ): AuditLog {
    return $this->logAudit(
      action: $action,
      communityId: $communityId,
      resourceType: 'Event',
      resourceId: $eventId,
      oldValues: $oldValues,
      newValues: $newValues,
      description: $description,
      request: $request
    );
  }

  /**
   * Generate default description for common actions
   */
  private function getDefaultDescription(string $action, ?string $resourceType, ?int $resourceId): string
  {
    $resource = $resourceType && $resourceId ? "{$resourceType} ID {$resourceId}" : "unknown resource";

    return match ($action) {
      'created' => "Created {$resource}",
      'updated' => "Updated {$resource}",
      'deleted' => "Deleted {$resource}",
      'approved' => "Approved {$resource}",
      'rejected' => "Rejected {$resource}",
      'role_assigned' => "Assigned role to {$resource}",
      'member_removed' => "Removed member {$resource}",
      'permission_granted' => "Permission granted for {$resource}",
      'permission_denied' => "Permission denied for {$resource}",
      default => "Performed action '{$action}' on {$resource}",
    };
  }
}

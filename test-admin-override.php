<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Http\Controllers\Api\CommunityMembershipController;
use App\Traits\HasCommunityPermissions;

class AdminOverrideTest
{
  use HasCommunityPermissions;

  public function runTest()
  {
    echo "Testing Website Admin Override Functionality\n";
    echo "==========================================\n\n";

    // Get admin user (user 1)
    $adminUser = User::find(1);
    if (!$adminUser) {
      echo "✗ Admin user not found\n";
      return;
    }

    echo "Testing with admin user: {$adminUser->first_name} {$adminUser->last_name}\n";
    echo "Is website admin: " . ($adminUser->isWebsiteAdmin() ? 'Yes' : 'No') . "\n\n";

    // Get a community to test with
    $community = Community::first();
    if (!$community) {
      echo "✗ No community found for testing\n";
      return;
    }

    echo "Testing with community: {$community->name} (ID: {$community->id})\n\n";

    // Test 1: Check if admin has permission even without membership
    echo "Test 1: Checking permissions without community membership\n";
    $hasViewPermission = $this->hasPermissionInCommunity($adminUser, $community->id, 'view_members');
    echo "Admin has 'view_members' permission: " . ($hasViewPermission ? 'Yes' : 'No') . "\n";

    $hasEditPermission = $this->hasPermissionInCommunity($adminUser, $community->id, 'edit_community');
    echo "Admin has 'edit_community' permission: " . ($hasEditPermission ? 'Yes' : 'No') . "\n";

    $hasDeletePermission = $this->hasPermissionInCommunity($adminUser, $community->id, 'delete_community');
    echo "Admin has 'delete_community' permission: " . ($hasDeletePermission ? 'Yes' : 'No') . "\n\n";

    // Test 2: Check requireCommunityPermission method
    echo "Test 2: Testing requireCommunityPermission method\n";
    $permissionError = $this->requireCommunityPermission($adminUser, $community->id, 'manage_attendance');
    if ($permissionError) {
      echo "✗ Admin was denied permission (this should not happen)\n";
      echo "Error response: " . $permissionError->content() . "\n";
    } else {
      echo "✓ Admin passed permission check for 'manage_attendance'\n";
    }

    $permissionError2 = $this->requireCommunityPermission($adminUser, $community->id, 'assign_roles');
    if ($permissionError2) {
      echo "✗ Admin was denied permission (this should not happen)\n";
      echo "Error response: " . $permissionError2->content() . "\n";
    } else {
      echo "✓ Admin passed permission check for 'assign_roles'\n";
    }

    echo "\n";

    // Test 3: Compare with a regular user
    echo "Test 3: Comparing with a regular user\n";
    $regularUser = User::where('id', '!=', 1)->first();
    if ($regularUser) {
      echo "Testing with regular user: {$regularUser->first_name} {$regularUser->last_name}\n";
      echo "Is website admin: " . ($regularUser->isWebsiteAdmin() ? 'Yes' : 'No') . "\n";

      $regularUserPermission = $this->hasPermissionInCommunity($regularUser, $community->id, 'view_members');
      echo "Regular user has 'view_members' permission: " . ($regularUserPermission ? 'Yes' : 'No') . "\n";

      $regularUserError = $this->requireCommunityPermission($regularUser, $community->id, 'delete_community');
      if ($regularUserError) {
        echo "✓ Regular user was correctly denied 'delete_community' permission\n";
      } else {
        echo "✗ Regular user incorrectly has 'delete_community' permission\n";
      }
    }

    echo "\n";

    // Test 4: Test with all available permissions
    echo "Test 4: Testing admin access to all permissions\n";
    $permissions = [
      'view_community',
      'edit_community',
      'delete_community',
      'view_members',
      'approve_members',
      'reject_members',
      'remove_members',
      'assign_roles',
      'view_events',
      'create_events',
      'edit_events',
      'delete_events',
      'manage_registrations',
      'view_registrations',
      'approve_registrations',
      'reject_registrations',
      'view_attendance',
      'manage_attendance',
      'generate_codes',
      'view_reports',
      'manage_gallery',
      'moderate_content',
      'send_notifications',
      'manage_announcements',
      'view_audit_logs'
    ];

    $allPassed = true;
    foreach ($permissions as $permission) {
      $hasPermission = $this->hasPermissionInCommunity($adminUser, $community->id, $permission);
      if (!$hasPermission) {
        echo "✗ Admin does not have permission: $permission\n";
        $allPassed = false;
      }
    }

    if ($allPassed) {
      echo "✓ Admin has access to all " . count($permissions) . " permissions\n";
    }

    echo "\n=== Test Results ===\n";
    if ($adminUser->isWebsiteAdmin() && $hasViewPermission && $hasEditPermission && $hasDeletePermission && !$permissionError && !$permissionError2 && $allPassed) {
      echo "✓ All tests passed! Website admin override is working correctly.\n";
    } else {
      echo "✗ Some tests failed. Please check the implementation.\n";
    }
  }
}

$test = new AdminOverrideTest();
$test->runTest();

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\CommunityRole;
use App\Models\CommunityRolePermission;

class CommunityPermissionsSeeder extends Seeder
{
  public function run()
  {
    // Define permissions for community management
    $permissions = [
      // Community Management
      ['name' => 'view_community', 'description' => 'View community details'],
      ['name' => 'edit_community', 'description' => 'Edit community information'],
      ['name' => 'delete_community', 'description' => 'Delete community'],

      // Member Management
      ['name' => 'view_members', 'description' => 'View community members'],
      ['name' => 'approve_members', 'description' => 'Approve membership applications'],
      ['name' => 'reject_members', 'description' => 'Reject membership applications'],
      ['name' => 'remove_members', 'description' => 'Remove members from community'],
      ['name' => 'assign_roles', 'description' => 'Assign roles to members'],

      // Event Management
      ['name' => 'view_events', 'description' => 'View community events'],
      ['name' => 'create_events', 'description' => 'Create new events'],
      ['name' => 'edit_events', 'description' => 'Edit existing events'],
      ['name' => 'delete_events', 'description' => 'Delete events'],
      ['name' => 'manage_registrations', 'description' => 'Manage event registrations'],
      ['name' => 'view_registrations', 'description' => 'View event registrations'],
      ['name' => 'approve_registrations', 'description' => 'Approve event registrations'],
      ['name' => 'reject_registrations', 'description' => 'Reject event registrations'],

      // Attendance Management
      ['name' => 'view_attendance', 'description' => 'View attendance records'],
      ['name' => 'manage_attendance', 'description' => 'Manage attendance check-ins'],
      ['name' => 'generate_codes', 'description' => 'Generate attendance codes'],
      ['name' => 'view_reports', 'description' => 'View attendance reports'],

      // Content Management
      ['name' => 'manage_gallery', 'description' => 'Manage community gallery'],
      ['name' => 'moderate_content', 'description' => 'Moderate community content'],

      // Communication
      ['name' => 'send_notifications', 'description' => 'Send notifications to members'],
      ['name' => 'manage_announcements', 'description' => 'Manage community announcements'],

      // Security & Audit
      ['name' => 'view_audit_logs', 'description' => 'View community audit logs'],
    ];

    // Create permissions
    foreach ($permissions as $permission) {
      Permission::firstOrCreate([
        'name' => $permission['name']
      ], [
        'description' => $permission['description'],
        'guard_name' => 'web'
      ]);
    }

    // Define role permissions
    $rolePermissions = [
      'Kurucu' => [ // Founder - All permissions
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
      ],
      'Yönetici' => [ // Admin - Most permissions except delete community
        'view_community',
        'edit_community',
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
      ],
      'Moderatör' => [ // Moderator - Limited permissions
        'view_community',
        'view_members',
        'approve_members',
        'reject_members',
        'view_events',
        'create_events',
        'edit_events',
        'view_registrations',
        'approve_registrations',
        'reject_registrations',
        'view_attendance',
        'manage_attendance',
        'moderate_content',
        'send_notifications'
      ],
      'Üye' => [ // Member - Basic permissions
        'view_community',
        'view_members',
        'view_events',
        'view_registrations'
      ]
    ];

    // Assign permissions to roles
    foreach ($rolePermissions as $roleName => $permissionNames) {
      $roles = CommunityRole::where('role', $roleName)->get();

      foreach ($roles as $role) {
        // Clear existing permissions for this role
        CommunityRolePermission::where('community_role_id', $role->id)->delete();

        // Assign new permissions
        foreach ($permissionNames as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();
          if ($permission) {
            CommunityRolePermission::firstOrCreate([
              'community_role_id' => $role->id,
              'permission_id' => $permission->id
            ]);
          }
        }
      }
    }

    echo "Community permissions and role assignments created successfully!\n";
  }
}

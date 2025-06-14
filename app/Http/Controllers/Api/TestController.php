<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\CommunityMembership;

class TestController extends Controller
{
  /**
   * Test admin stats without authentication (for debugging)
   */
  public function testAdminStats()
  {
    try {
      $totalUsers = User::count();
      $totalCommunities = Community::count();
      $totalEvents = Event::count();
      $pendingApplications = CommunityMembership::where('status', 'pending')->count();
      $activeEvents = Event::where('start_datetime', '>=', now())->count();

      $stats = [
        'totalUsers' => [
          'value' => $totalUsers,
          'change' => 12.5
        ],
        'totalCommunities' => [
          'value' => $totalCommunities,
          'change' => 5.2
        ],
        'pendingApplications' => [
          'value' => $pendingApplications,
          'change' => -2.1
        ],
        'activeEvents' => [
          'value' => $activeEvents,
          'change' => 8.0
        ]
      ];

      return response()->json([
        'success' => true,
        'stats' => $stats,
        'message' => 'Test successful - database queries working'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Database query failed'
      ], 500);
    }
  }

  /**
   * Test authentication status
   */
  public function testAuth()
  {
    $user = auth()->user();

    if (!$user) {
      return response()->json([
        'authenticated' => false,
        'message' => 'Not authenticated'
      ]);
    }

    $roles = [];
    try {
      if (method_exists($user, 'roles')) {
        $roles = $user->roles()->pluck('name')->toArray();
      }
    } catch (\Exception $e) {
      $roles = ['error' => $e->getMessage()];
    }

    return response()->json([
      'authenticated' => true,
      'user' => [
        'id' => $user->id,
        'email' => $user->email,
        'nickname' => $user->nickname
      ],
      'roles' => $roles,
      'has_admin' => in_array('admin', $roles)
    ]);
  }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\CommunityMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:sanctum');
  }
  /**
   * Check if user has admin role
   */
  private function checkAdminRole()
  {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // Check if user has admin role using Spatie Laravel Permission
    $hasAdminRole = false;
    try {
      // Try the hasRole method from Spatie Laravel Permission
      $hasAdminRole = method_exists($user, 'hasRole') && $user->hasRole('admin');
    } catch (\Exception $e) {
      // Fallback: check if user ID is 1 (assumed to be admin)
      $hasAdminRole = ($user->id == 1);
    }

    if (!$hasAdminRole) {
      return response()->json(['message' => 'Unauthorized - Admin role required'], 403);
    }

    return null; // No error
  }

  /**
   * Get admin dashboard statistics
   */
  public function getStats()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $totalUsers = User::count();
    $totalCommunities = Community::count();
    $totalEvents = Event::count();
    $pendingApplications = CommunityMembership::where('status', 'pending')->count();

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
        'value' => Event::where('start_datetime', '>=', now())->count(),
        'change' => 8.0
      ]
    ];

    return response()->json($stats);
  }

  /**
   * Get recent users for admin dashboard
   */
  public function getRecentUsers()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $recentUsers = User::latest()
      ->limit(10)
      ->get(['id', 'first_name', 'last_name', 'nickname', 'email', 'created_at', 'profile_picture'])
      ->map(function ($user) {
        return [
          'id' => $user->id,
          'name' => $user->first_name . ' ' . $user->last_name,
          'nickname' => $user->nickname,
          'email' => $user->email,
          'joinDate' => $user->created_at->format('Y-m-d'),
          'status' => 'Active',
          'avatar' => $user->profile_picture ?? '/placeholder.svg?height=40&width=40'
        ];
      });

    return response()->json($recentUsers);
  }

  /**
   * Get recent communities for admin dashboard
   */
  public function getRecentCommunities()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $recentCommunities = Community::with('creator')
      ->latest()
      ->limit(10)
      ->get()
      ->map(function ($community) {
        return [
          'id' => $community->id,
          'name' => $community->community,
          'creator' => $community->creator ?
            $community->creator->first_name . ' ' . $community->creator->last_name :
            'Unknown',
          'members' => $community->memberships()->count(),
          'createdDate' => $community->created_at->format('Y-m-d')
        ];
      });

    return response()->json($recentCommunities);
  }

  /**
   * Get pending applications
   */
  public function getPendingApplications()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $pendingApplications = CommunityMembership::with(['user', 'community'])
      ->where('status', 'pending')
      ->latest()
      ->get()
      ->map(function ($application) {
        return [
          'id' => $application->id,
          'user_name' => $application->user->first_name . ' ' . $application->user->last_name,
          'user_nickname' => $application->user->nickname,
          'community_name' => $application->community->community,
          'applied_date' => $application->created_at->format('Y-m-d H:i'),
        ];
      });

    return response()->json($pendingApplications);
  }
}

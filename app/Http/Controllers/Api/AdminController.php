<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\CommunityMembership;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

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
    $user = auth()->user();
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
  /**
   * Get all events for admin management
   */
  public function getEvents()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $events = Event::with(['community'])
      ->latest()
      ->get()
      ->map(function ($event) {
        return [
          'id' => $event->id,
          'event' => $event->event,
          'cover_image' => $event->cover_image_url, // Use the URL
          'community_id' => $event->community_id,
          'community' => $event->community ? [
            'id' => $event->community->id,
            'community' => $event->community->community
          ] : null,
          'description' => $event->description,
          'start_datetime' => $event->start_datetime,
          'location' => $event->location,
          'created_at' => $event->created_at
        ];
      });

    return response()->json(['events' => $events]);
  }
  /**
   * Get all users for admin management
   */
  public function getUsers()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $users = User::select([
        'id', 
        'first_name', 
        'last_name', 
        'email', 
        'nickname',
        'profile_picture', // Add this field
        'created_at'
      ])
      ->latest()
      ->get()
      ->map(function ($user) {
        return [
          'id' => $user->id,
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'email' => $user->email,
          'nickname' => $user->nickname,
          'profile_picture' => $user->profile_picture_url, // Use the URL
          'created_at' => $user->created_at
        ];
      });

    return response()->json(['users' => $users]);
  }
  /**
   * Get communities for admin management
   */
  public function getCommunities()
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $communities = Community::latest()
      ->get(['id', 'community', 'logo', 'about', 'created_at']) // Use correct column names
      ->map(function ($community) {
        return [
          'id' => $community->id,
          'community' => $community->community, // Use correct field name
          'logo' => $community->logo_url, // Use the URL
          'about' => $community->about, // Use correct field name
          'created_at' => $community->created_at
        ];
      });

    return response()->json(['communities' => $communities]);
  }

  /**
   * Get check-in stats for an event
   */
  public function getEventCheckInStats($eventId)
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $event = Event::with(['registrations.user'])->find($eventId);
    if (!$event) {
      return response()->json(['message' => 'Event not found'], 404);
    }

    $registrations = $event->registrations;
    $checkedInCount = $registrations->where('checked_in_at', '!=', null)->count();
    $totalRegistrations = $registrations->count();
    
    $attendanceRate = $totalRegistrations > 0 ? 
      round(($checkedInCount / $totalRegistrations) * 100, 2) : 0;

    $recentCheckIns = $registrations
      ->where('checked_in_at', '!=', null)
      ->sortByDesc('checked_in_at')
      ->take(5)
      ->map(function ($registration) {
        return [
          'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
          'checked_in_at' => $registration->checked_in_at,
          'checked_in_by' => $registration->checked_in_by ?: 'Self'
        ];
      })
      ->values();

    return response()->json([
      'event' => $event->event,
      'total_registrations' => $totalRegistrations,
      'checked_in_count' => $checkedInCount,
      'attendance_rate' => $attendanceRate,
      'pending_check_ins' => $totalRegistrations - $checkedInCount,
      'recent_check_ins' => $recentCheckIns
    ]);
  }

  /**
   * Check in a user by attendance code
   */
  public function checkInByCode(Request $request, $eventId)
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $request->validate([
      'attendance_code' => 'required|string|size:8',
      'notes' => 'nullable|string|max:500'
    ]);

    $attendanceCode = strtoupper($request->attendance_code);
    $notes = $request->notes;

    // Find the registration by attendance code
    $registration = EventRegistration::where('attendance_code', $attendanceCode)
      ->with(['user', 'event'])
      ->first();

    if (!$registration) {
      return response()->json([
        'message' => 'Invalid attendance code'
      ], 404);
    }

    // Check if the code belongs to the correct event
    if ($registration->event_id != $eventId) {
      return response()->json([
        'message' => 'This attendance code belongs to a different event: ' . $registration->event->event
      ], 400);
    }

    // Check if already checked in
    if ($registration->isCheckedIn()) {
      return response()->json([
        'message' => 'User is already checked in',
        'checked_in_at' => $registration->checked_in_at,
        'registration' => $registration
      ], 409);
    }

    // Check in the user
    $registration->checkIn(
      auth()->user()->first_name . ' ' . auth()->user()->last_name,
      $notes
    );

    return response()->json([
      'message' => 'Check-in successful',
      'registration' => $registration->fresh(['user', 'event'])
    ]);
  }

  /**
   * Bulk check-in users by attendance codes
   */
  public function bulkCheckIn(Request $request, $eventId)
  {
    $check = $this->checkAdminRole();
    if ($check) return $check;

    $request->validate([
      'attendance_codes' => 'required|array|min:1',
      'attendance_codes.*' => 'required|string|size:8',
      'notes' => 'nullable|string|max:500'
    ]);

    $codes = array_map('strtoupper', $request->attendance_codes);
    $notes = $request->notes;
    $checkedInBy = auth()->user()->first_name . ' ' . auth()->user()->last_name;

    $summary = [
      'successfully_checked_in' => 0,
      'already_checked_in' => 0,
      'not_found' => 0,
      'wrong_event' => 0
    ];

    $results = [];

    foreach ($codes as $code) {
      $registration = EventRegistration::where('attendance_code', $code)
        ->with(['user', 'event'])
        ->first();

      if (!$registration) {
        $summary['not_found']++;
        $results[] = [
          'code' => $code,
          'status' => 'not_found',
          'message' => 'Invalid attendance code'
        ];
        continue;
      }

      if ($registration->event_id != $eventId) {
        $summary['wrong_event']++;
        $results[] = [
          'code' => $code,
          'status' => 'wrong_event',
          'message' => 'Belongs to event: ' . $registration->event->event,
          'user' => $registration->user->first_name . ' ' . $registration->user->last_name
        ];
        continue;
      }

      if ($registration->isCheckedIn()) {
        $summary['already_checked_in']++;
        $results[] = [
          'code' => $code,
          'status' => 'already_checked_in',
          'message' => 'Already checked in',
          'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
          'checked_in_at' => $registration->checked_in_at
        ];
        continue;
      }

      // Check in the user
      $registration->checkIn($checkedInBy, $notes);
      $summary['successfully_checked_in']++;
      $results[] = [
        'code' => $code,
        'status' => 'success',
        'message' => 'Check-in successful',
        'user' => $registration->user->first_name . ' ' . $registration->user->last_name
      ];
    }

    return response()->json([
      'message' => 'Bulk check-in completed',
      'summary' => $summary,
      'results' => $results
    ]);
  }
}

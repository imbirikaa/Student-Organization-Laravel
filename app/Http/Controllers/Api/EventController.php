<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use App\Notifications\EventRegistrationCancelled;
use App\Notifications\EventRegistrationConfirmed;
use App\Traits\HasCommunityPermissions;

class EventController extends Controller
{
    use HasCommunityPermissions;
    public function index()
    {
        return Event::paginate(5);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $request->validate([
            'community_id' => 'required|exists:communities,id',
            'event' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'location' => 'required|string|max:255',
        ]);

        // Check if user has permission to create events in this community
        $communityId = $request->community_id;
        $permissionError = $this->requireCommunityPermission($user, $communityId, 'create_events');
        if ($permissionError) {
            return $permissionError;
        }

        return Event::create($request->all());
    }

    public function show(Event $event)
    {
        $event->load('community');
        return $event;
    }

    public function showByCommunity($communityId)
    {
        return response()->json(
            Event::where('community_id', $communityId)->paginate(5)
        );
    }

    public function update(Request $request, Event $event)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // Check if user has permission to edit events in this community
        $permissionError = $this->requireCommunityPermission($user, $event->community_id, 'edit_events');
        if ($permissionError) {
            return $permissionError;
        }

        $event->update($request->all());
        return $event;
    }

    public function destroy(Event $event)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // Check if user has permission to delete events in this community
        $permissionError = $this->requireCommunityPermission($user, $event->community_id, 'delete_events');
        if ($permissionError) {
            return $permissionError;
        }

        $event->delete();
        return response()->noContent();
    }

    /**
     * Register user for an event
     */
    public function register(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user is already registered
        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return response()->json(['message' => 'Already registered for this event'], 409);
        }

        // Create registration
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_date' => now(),
            'status' => 'confirmed'
        ]);

        return response()->json([
            'message' => 'Successfully registered for event',
            'registration' => $registration
        ], 201);
    }

    /**
     * Unregister user from an event
     */
    public function unregister(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Not registered for this event'], 404);
        }

        $registration->delete();

        return response()->json(['message' => 'Successfully unregistered from event']);
    }

    /**
     * Get event registrations (for admin/organizers)
     */
    public function getRegistrations(Event $event)
    {
        $registrations = EventRegistration::with('user')
            ->where('event_id', $event->id)
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user_name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                    'user_email' => $registration->user->email,
                    'registration_date' => $registration->registration_date,
                    'status' => $registration->status
                ];
            });

        return response()->json([
            'event' => $event->event,
            'total_registrations' => $registrations->count(),
            'registrations' => $registrations
        ]);
    }

    /**
     * Check if user is registered for an event
     */
    public function checkRegistration(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['registered' => false]);
        }

        $isRegistered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json(['registered' => $isRegistered]);
    }

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
     * Get all event registrations for admin review
     */
    public function getAllRegistrations(Request $request)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = EventRegistration::with(['user', 'event.community']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event if provided
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $registrations = $query->orderBy('registration_date', 'desc')
            ->paginate(20)
            ->through(function ($registration) {
                return [
                    'id' => $registration->id,
                    'user' => [
                        'id' => $registration->user->id,
                        'name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                        'email' => $registration->user->email,
                    ],
                    'event' => [
                        'id' => $registration->event->id,
                        'name' => $registration->event->event,
                        'start_datetime' => $registration->event->start_datetime,
                        'location' => $registration->event->location,
                        'community' => $registration->event->community->community,
                    ],
                    'registration_date' => $registration->registration_date,
                    'status' => $registration->status,
                ];
            });

        return response()->json($registrations);
    }

    /**
     * Update registration status (admin only)
     */
    public function updateRegistrationStatus(Request $request, EventRegistration $registration)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:confirmed,cancelled,waitlist,attended'
        ]);

        $registration->update([
            'status' => $request->status
        ]);

        // Send notification for status change
        if ($request->status === 'confirmed') {
            $registration->user->notify(new EventRegistrationConfirmed($registration->event));
        } elseif ($request->status === 'cancelled') {
            $registration->user->notify(new EventRegistrationCancelled($registration->event));
        }

        return response()->json([
            'message' => 'Registration status updated successfully',
            'registration' => $registration->load(['user', 'event'])
        ]);
    }

    /**
     * Cancel registration (admin only)
     */
    public function cancelRegistration(Request $request, EventRegistration $registration)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $registration->update(['status' => 'cancelled']);

        // Send notification to user
        $registration->user->notify(new EventRegistrationCancelled($registration->event, 'Registration cancelled by administrator'));

        return response()->json([
            'message' => 'Registration cancelled successfully',
            'registration' => $registration->load(['user', 'event'])
        ]);
    }

    /**
     * Mark registration as attended (admin only)
     */
    public function markAttended(Request $request, EventRegistration $registration)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $registration->update(['status' => 'attended']);

        return response()->json([
            'message' => 'Registration marked as attended',
            'registration' => $registration->load(['user', 'event'])
        ]);
    }

    /**
     * Get event registration statistics
     */
    public function getRegistrationStats(Request $request)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_registrations' => EventRegistration::count(),
            'confirmed_registrations' => EventRegistration::where('status', 'confirmed')->count(),
            'cancelled_registrations' => EventRegistration::where('status', 'cancelled')->count(),
            'attended_registrations' => EventRegistration::where('status', 'attended')->count(),
            'recent_registrations' => EventRegistration::where('registration_date', '>=', now()->subDays(7))->count(),
            'upcoming_events' => Event::where('start_datetime', '>', now())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export event registrations (admin only)
     */
    public function exportRegistrations(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $registrations = EventRegistration::with('user')
            ->where('event_id', $event->id)
            ->get()
            ->map(function ($registration) {
                return [
                    'Name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                    'Email' => $registration->user->email,
                    'Registration Date' => $registration->registration_date->format('Y-m-d H:i:s'),
                    'Status' => ucfirst($registration->status),
                ];
            });

        return response()->json([
            'event' => $event->event,
            'registrations' => $registrations,
            'export_date' => now()->toDateTimeString()
        ]);
    }

    /**
     * Check in by attendance code for a specific event
     */
    public function checkInByCode(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'attendance_code' => 'required|string|size:8',
            'notes' => 'nullable|string|max:500'
        ]);

        // Find the registration that matches BOTH the event ID and attendance code
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('attendance_code', strtoupper($request->attendance_code))
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Invalid attendance code for this event',
                'details' => 'The attendance code does not exist or does not belong to this event',
                'event' => $event->title
            ], 404);
        }

        if ($registration->isCheckedIn()) {
            return response()->json([
                'message' => 'Already checked in',
                'checked_in_at' => $registration->checked_in_at,
                'checked_in_by' => $registration->checked_in_by,
                'event' => $registration->event->title
            ], 409);
        }

        $registration->checkIn($user->email, $request->notes);

        return response()->json([
            'message' => 'Successfully checked in',
            'registration' => $registration->load(['user', 'event']),
            'checked_in_at' => $registration->checked_in_at,
            'event' => [
                'id' => $registration->event->id,
                'title' => $registration->event->title,
                'location' => $registration->event->location
            ]
        ]);
    }

    /**
     * Get attendance code for a registration (for QR code generation or display)
     */
    public function getAttendanceCode(EventRegistration $registration)
    {
        $user = request()->user();

        // Only allow the registered user or admin to see the attendance code
        if (!$this->isAdmin($user) && $registration->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'registration_id' => $registration->id,
            'attendance_code' => $registration->attendance_code,
            'event' => $registration->event->event,
            'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
            'qr_data' => json_encode([
                'registration_id' => $registration->id,
                'attendance_code' => $registration->attendance_code,
                'event_id' => $registration->event_id,
                'user_id' => $registration->user_id,
                'timestamp' => now()->timestamp
            ])
        ]);
    }

    /**
     * Bulk check-in multiple registrations for a specific event
     */
    public function bulkCheckIn(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'attendance_codes' => 'required|array|min:1',
            'attendance_codes.*' => 'string|size:8',
            'notes' => 'nullable|string|max:500'
        ]);

        $codes = array_map('strtoupper', $request->attendance_codes);

        // Only get registrations that belong to the specific event
        $registrations = EventRegistration::where('event_id', $event->id)
            ->whereIn('attendance_code', $codes)
            ->get();

        $results = [
            'checked_in' => [],
            'already_checked_in' => [],
            'not_found' => [],
            'wrong_event' => []
        ];

        foreach ($codes as $code) {
            $registration = $registrations->where('attendance_code', $code)->first();

            if (!$registration) {
                // Check if the code exists for a different event
                $existsElsewhere = EventRegistration::where('attendance_code', $code)
                    ->where('event_id', '!=', $event->id)
                    ->exists();

                if ($existsElsewhere) {
                    $results['wrong_event'][] = $code;
                } else {
                    $results['not_found'][] = $code;
                }
                continue;
            }

            if ($registration->isCheckedIn()) {
                $results['already_checked_in'][] = [
                    'code' => $code,
                    'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
                    'checked_in_at' => $registration->checked_in_at
                ];
                continue;
            }

            $registration->checkIn($user->email, $request->notes);
            $results['checked_in'][] = [
                'code' => $code,
                'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
                'checked_in_at' => $registration->checked_in_at
            ];
        }

        return response()->json([
            'message' => 'Bulk check-in completed',
            'event' => [
                'id' => $event->id,
                'title' => $event->title
            ],
            'results' => $results,
            'summary' => [
                'total_processed' => count($codes),
                'successfully_checked_in' => count($results['checked_in']),
                'already_checked_in' => count($results['already_checked_in']),
                'not_found' => count($results['not_found']),
                'wrong_event' => count($results['wrong_event'])
            ]
        ]);
    }

    /**
     * Get check-in statistics for an event
     */
    public function getCheckInStats(Event $event)
    {
        $user = request()->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalRegistrations = EventRegistration::where('event_id', $event->id)->count();
        $checkedInCount = EventRegistration::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->count();

        $recentCheckIns = EventRegistration::with('user')
            ->where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($registration) {
                return [
                    'user' => $registration->user->first_name . ' ' . $registration->user->last_name,
                    'checked_in_at' => $registration->checked_in_at,
                    'checked_in_by' => $registration->checked_in_by
                ];
            });

        return response()->json([
            'event' => $event->event,
            'total_registrations' => $totalRegistrations,
            'checked_in_count' => $checkedInCount,
            'attendance_rate' => $totalRegistrations > 0 ? round(($checkedInCount / $totalRegistrations) * 100, 2) : 0,
            'pending_check_ins' => $totalRegistrations - $checkedInCount,
            'recent_check_ins' => $recentCheckIns
        ]);
    }

    /**
     * Get user's attendance codes for their event registrations
     */
    public function getMyAttendanceCodes()
    {
        try {
            $user = request()->user();

            $registrations = EventRegistration::with(['event', 'user'])
                ->where('user_id', $user->id)
                ->whereNotNull('attendance_code')
                ->where('status', 'confirmed')
                ->get()
                ->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'attendance_code' => $registration->attendance_code,
                        'checked_in_at' => $registration->checked_in_at,
                        'checked_in_by' => $registration->checked_in_by,
                        'check_in_notes' => $registration->check_in_notes,
                        'event' => [
                            'id' => $registration->event->id,
                            'event' => $registration->event->title, // Use title accessor for display
                            'description' => $registration->event->description,
                            'start_date' => $registration->event->start_date,
                            'end_date' => $registration->event->end_date,
                            'location' => $registration->event->location,
                            'max_participants' => $registration->event->max_participants,
                        ],
                        'user' => [
                            'id' => $registration->user->id,
                            'name' => $registration->user->name,
                            'email' => $registration->user->email,
                        ],
                        'registration_date' => $registration->registration_date,
                        'status' => $registration->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $registrations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance codes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific attendance code details
     */
    public function getAttendanceCodeDetails($code)
    {
        try {
            $registration = EventRegistration::with(['event', 'user'])
                ->where('attendance_code', $code)
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid attendance code'
                ], 404);
            }

            // Check if user owns this registration or is admin
            $user = request()->user();
            if ($registration->user_id !== $user->id && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $registration->id,
                    'attendance_code' => $registration->attendance_code,
                    'event' => [
                        'id' => $registration->event->id,
                        'title' => $registration->event->title,
                        'start_date' => $registration->event->start_date,
                        'end_date' => $registration->event->end_date,
                        'location' => $registration->event->location,
                        'description' => $registration->event->description,
                    ],
                    'user' => [
                        'name' => $registration->user->name,
                        'email' => $registration->user->email,
                    ],
                    'registration_date' => $registration->registration_date,
                    'checked_in' => !is_null($registration->checked_in_at),
                    'checked_in_at' => $registration->checked_in_at,
                    'checked_in_by' => $registration->checked_in_by,
                    'check_in_notes' => $registration->check_in_notes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance code details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all events for admin use (no pagination)
     */
    public function getAllEvents(Request $request)
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $events = Event::with('community')
            ->orderBy('start_datetime', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event' => $event->event,
                    'start_datetime' => $event->start_datetime,
                    'location' => $event->location,
                    'community' => $event->community->community,
                    'status' => $event->status ?? 'active',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }
}

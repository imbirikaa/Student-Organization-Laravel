<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        return Event::paginate(5);
    }

    public function store(Request $request)
    {
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
        $event->update($request->all());
        return $event;
    }

    public function destroy(Event $event)
    {
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
}

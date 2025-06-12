<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
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
}

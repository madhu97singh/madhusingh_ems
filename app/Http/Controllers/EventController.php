<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
{
    $events = Event::where('user_id', Auth::id())->with('guests')->get();
    return response()->json($events);
}

public function store(Request $request)
{
    $this->validate($request, [
        'name' => 'required|unique:events',
        'description' => 'nullable',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'guests' => 'required|array',
        'guests.*.name' => 'required|string',
        'guests.*.email' => 'required|email',
    ]);

    $event = Event::create([
        'name' => $request->name,
        'description' => $request->description,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'user_id' => Auth::id(),
    ]);

    foreach ($request->guests as $guestData) {
        Guest::create([
            'event_id' => $event->id,
            'name' => $guestData['name'],
            'email' => $guestData['email'],
        ]);
    }

    return response()->json($event, 201);
}

public function update(Request $request, Event $event)
{
    $this->validate($request, [
        'name' => 'required|unique:events,name,' . $event->id,
        'description' => 'nullable',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ]);

    $event->update($request->all());

    return response()->json($event);
}

public function destroy(Event $event)
{
    $event->guests()->delete();
    $event->delete();

    return response()->json(null, 204);
}
}

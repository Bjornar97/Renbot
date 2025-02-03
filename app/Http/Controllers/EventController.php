<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Event::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $events = Event::select(DB::raw('DATE(start) as date'), 'id', 'title', 'slug', 'start', 'end', 'description', 'type')
            ->upcoming()
            ->orderBy('start')
            ->get()
            ->groupBy(function ($event) {
                return $event->date; // Group by the extracted date
            });

        // Format the grouped data as an array with "date" and "events" keys
        $days = $events->map(function ($events, $date) {
            return [
                'date' => $date,
                'events' => $events->toArray(), // Convert each group to an array of events
            ];
        })->values()->toArray();

        return Inertia::render('Events/Index', [
            'days' => $days,
            'userType' => $request->user()?->type ?? 'viewer',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Events/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::query()->create($request->validated());

        return redirect()->route('events.edit', $event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event)
    {
        return Inertia::render('Events/Show', [
            'event' => $event->load('participants', 'teams'),
            'userType' => $request->user()?->type ?? 'viewer',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());

        return redirect()->route('events.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return back()->with('success', 'Successfully deleted event');
    }
}

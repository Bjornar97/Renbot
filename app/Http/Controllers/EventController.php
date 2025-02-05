<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Event::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $events = Event::select(DB::raw('DATE(start) as date'), 'id', 'title', 'slug', 'start', 'end', 'description', 'type')
            ->upcoming()
            ->orderBy('start')
            ->get()
            ->groupBy(function ($event): string {
                // @phpstan-ignore-next-line
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
            'userType' => $request->user()->type ?? 'viewer',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Events/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = Event::query()->create($request->validated());

        return redirect()->route('events.edit', $event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event): Response
    {
        return Inertia::render('Events/Show', [
            'event' => $event->load('participants', 'teams'),
            'userType' => $request->user()->type ?? 'viewer',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event): Response
    {
        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('events.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return back()->with('success', 'Successfully deleted event');
    }
}

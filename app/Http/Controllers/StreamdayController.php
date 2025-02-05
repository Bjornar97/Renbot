<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStreamdayRequest;
use App\Http\Requests\UpdateStreamdayRequest;
use App\Models\Creator;
use App\Models\Streamday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StreamdayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('moderate');

        return Inertia::render('Streamdays/Index', [
            'streamdays' => Streamday::query()->where('end_date', '>', now()->subDay())->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('moderate');

        return Inertia::render('Streamdays/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStreamdayRequest $request): RedirectResponse
    {
        $streamday = Streamday::query()->create($request->validated());

        return redirect()->route('streamdays.edit', ['streamday' => $streamday])
            ->with('success', 'Successfully created Streamday!');
    }

    /**
     * Display the specified resource.
     */
    public function show(): Response
    {
        $currentStreamday = Streamday::query()
            ->whereRelation('streamdaySlots', 'end_at', '>', now())
            ->orderBy('start_date')->with('streamdaySlots')
            ->first();

        return Inertia::render('Streamdays/Show', [
            'streamday' => $currentStreamday,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Streamday $streamday): Response
    {
        return Inertia::render('Streamdays/Edit', [
            'streamday' => $streamday->load('streamdaySlots'),
            'creators' => Creator::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStreamdayRequest $request, Streamday $streamday): RedirectResponse
    {
        $streamday->update($request->validated());

        return back()->with('success', 'Successfully updated streamday');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Streamday $streamday): RedirectResponse
    {
        $streamday->delete();

        return back()->with('success', 'Successfully deleted streamday');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStreamdaySlotRequest;
use App\Http\Requests\UpdateStreamdaySlotRequest;
use App\Models\Streamday;
use App\Models\StreamdaySlot;
use Illuminate\Support\Facades\Gate;

class StreamdaySlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStreamdaySlotRequest $request, Streamday $streamday)
    {
        $slot = $streamday->streamdaySlots()->create($request->validated());

        return back()->with('success', 'Successfully added slot');
    }

    /**
     * Display the specified resource.
     */
    public function show(StreamdaySlot $slot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StreamdaySlot $slot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStreamdaySlotRequest $request, Streamday $streamday, StreamdaySlot $slot)
    {
        $slot->update($request->validated());

        return back()->with('success', 'Successfully updated slot');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Streamday $streamday, StreamdaySlot $slot)
    {
        Gate::authorize('moderate');

        $slot->delete();

        return back()->with('success', 'Successfully deleted slot');
    }
}

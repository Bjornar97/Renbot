<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStreamdaySlotRequest;
use App\Http\Requests\UpdateStreamdaySlotRequest;
use App\Models\Streamday;
use App\Models\StreamdaySlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StreamdaySlotController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStreamdaySlotRequest $request, Streamday $streamday): RedirectResponse
    {
        $slot = $streamday->streamdaySlots()->create($request->validated());

        return back()->with('success', 'Successfully added slot');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStreamdaySlotRequest $request, Streamday $streamday, StreamdaySlot $slot): RedirectResponse
    {
        $slot->update($request->validated());

        return back()->with('success', 'Successfully updated slot');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Streamday $streamday, StreamdaySlot $slot): RedirectResponse
    {
        Gate::authorize('moderate');

        $slot->delete();

        return back()->with('success', 'Successfully deleted slot');
    }
}

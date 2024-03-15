<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCreatorRequest;
use App\Http\Requests\UpdateCreatorRequest;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CreatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("Creators/Index", [
            'creators' => Creator::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Creators/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreatorRequest $request)
    {
        $creator = Creator::query()->create($request->except('image'));

        $image = $request->validated('image')[0] ?? null;

        if ($image) {
            $path = $image->store('public/creators');
            $creator->image = $path;
            $creator->save();
        }

        return redirect(route('creators.index'))->with('success', 'Successfully created new Creator');
    }

    /**
     * Display the specified resource.
     */
    public function show(Creator $creator)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Creator $creator)
    {
        return Inertia::render("Creators/Edit", [
            'creator' => $creator,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreatorRequest $request, Creator $creator)
    {
        $creator->update($request->except('image'));

        $image = $request->validated('image')[0] ?? null;

        if ($image) {
            if ($creator->image) {
                Storage::delete($creator->image);
            }

            $path = $image->store('public/creators');
            $creator->image = $path;
            $creator->save();
        }

        return redirect(route('creators.index'))->with('success', "Successfully updated Creator {$creator->name}");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Creator $creator)
    {
        Gate::authorize("moderate");

        $creator->delete();

        return back()->with('success', "Creator was successfully deleted");
    }
}

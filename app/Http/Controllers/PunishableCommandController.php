<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommandRequest;
use App\Models\AutoPost;
use App\Models\Command;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PunishableCommandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render("Commands/Index", [
            'commands' => Command::punishable()->where('parent_id', null)->with('children')->orderBy('command')->get(),
            'type' => 'punishable',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render("Commands/Create", [
            'type' => 'punishable',
            'autoPosts' => AutoPost::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCommandRequest $request)
    {
        $data = $request->validated();
        $aliases = $request->validated('aliases');

        $command = Command::create($data);

        $command->children()->whereNotIn('command', $aliases)->delete();

        foreach ($aliases as $alias) {
            $command->children()->where('command', $alias)->updateOrCreate([
                'command' => $alias,
            ], [
                'usable_by' => $command->usable_by,
                'enabled' => true,
                'type' => $command->type,
            ]);
        }

        return redirect()->route("punishable-commands.index")->with("success", "The punishable command was successfully created!");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function show(Command $command)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function edit(Command $command)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Command $command)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function destroy(Command $command)
    {
        //
    }
}

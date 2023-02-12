<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommandRequest;
use App\Http\Requests\UpdateCommandRequest;
use App\Models\Command;
use App\Services\SpecialCommandService;
use Inertia\Inertia;

class CommandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Command::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render("Commands/Index", [
            'commands' => Command::regular()->orderBy('command')->get(),
            'type' => 'regular',
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
            'type' => 'regular',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCommandRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCommandRequest $request)
    {
        $data = $request->validated();

        $command = Command::create($data);

        return redirect()
            ->route("commands.index")
            ->with("success", "The new command {$command->command} was saved");
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
        return Inertia::render("Commands/Edit", [
            'command' => $command,
            'actions' => array_values(SpecialCommandService::$functions),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCommandRequest  $request
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommandRequest $request, Command $command)
    {
        $data = $request->validated();

        $command->update($data);

        return back()->with("success", "Successfully updated command");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function destroy(Command $command)
    {
        $command->delete();

        return back()->with("success", "Successfully deleted command");
    }
}

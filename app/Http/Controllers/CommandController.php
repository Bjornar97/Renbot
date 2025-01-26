<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCommandToChatRequest;
use App\Http\Requests\StoreCommandRequest;
use App\Http\Requests\UpdateCommandRequest;
use App\Jobs\SingleChatMessageJob;
use App\Models\AutoPost;
use App\Models\Command;
use App\Services\SpecialCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            'commands' => Command::regular()->where('parent_id', null)->with('children')->orderBy('command')->get(),
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
            'autoPosts' => AutoPost::all(),
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
        $commandName = "";

        DB::transaction(function ()  use ($request, &$commandName) {
            $commandData = $request->safe()->except(["auto_post", "aliases"]);
            $autoPostData = $request->validated("auto_post");
            $aliases = $request->validated("aliases");

            /** @var Command $command */
            $command = Command::create($commandData);
            $commandName = $command->command;

            if ($command->auto_post_enabled && $autoPostData) {
                $command->autoPost()->update($autoPostData);
            }

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
        });

        return redirect()
            ->route("commands.index")
            ->with("success", "The new command {$commandName} was saved");
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
            'command' => $command->load("autoPost", "children"),
            'actions' => array_values(SpecialCommandService::$functions),
            'autoPosts' => AutoPost::all(),
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
        $commandData = $request->safe()->except(["auto_post", "aliases"]);
        $autoPostData = $request->validated("auto_post");
        $aliases = $request->validated('aliases');

        $command->update($commandData);

        if ($autoPostData) {
            $command->autoPost?->update($autoPostData);
        }

        if ($aliases !== null) {
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
        }

        return back()->with("success", "Successfully updated command");
    }

    public function chat(SendCommandToChatRequest $request, Command $command)
    {
        $data = $request->validated();

        try {
            $message = $command->general_response;
            SingleChatMessageJob::dispatch($data['type'] ?? 'chat', $message, null, $data['announcement_color'] ?? null);
        } catch (\Throwable $th) {
            return back()->with('error', "Something went wrong: {$th->getMessage()}");
        }

        return back()->with("success", "Sending command to chat!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function destroy(Command $command)
    {
        $type = $command->type;

        $command->delete();

        if ($type === "regular") {
            $route = "commands.index";
        }

        if ($type === "punishable") {
            $route = "punishable-commands.index";
        }

        if ($type === "special") {
            $route = "special-commands.index";
        }

        return redirect()->route($route)->with("success", "Successfully deleted command");
    }
}

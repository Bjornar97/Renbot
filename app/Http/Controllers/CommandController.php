<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCommandToChatRequest;
use App\Http\Requests\StoreCommandRequest;
use App\Http\Requests\UpdateCommandRequest;
use App\Jobs\SingleChatMessageJob;
use App\Models\AutoPost;
use App\Models\Command;
use App\Services\SpecialCommandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Nightwatch\Facades\Nightwatch;

class CommandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Command::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Commands/Index', [
            'commands' => Command::regular()->where('parent_id', null)->with('children')->orderBy('command')->get(),
            'type' => 'regular',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Commands/Create', [
            'type' => 'regular',
            'autoPosts' => AutoPost::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommandRequest $request): RedirectResponse
    {
        $commandName = '';

        DB::transaction(function () use ($request, &$commandName) {
            $commandData = $request->safe()->except(['auto_post', 'aliases']);
            $autoPostData = $request->validated('auto_post');
            $aliases = $request->validated('aliases');

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
            ->route('commands.index')
            ->with('success', "The new command {$commandName} was saved");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Command $command): Response
    {
        return Inertia::render('Commands/Edit', [
            'command' => $command->load('autoPost', 'children'),
            'actions' => array_values(SpecialCommandService::$functions),
            'autoPosts' => AutoPost::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommandRequest $request, Command $command): RedirectResponse
    {
        $commandData = $request->safe()->except(['auto_post', 'aliases']);
        $autoPostData = $request->validated('auto_post');
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

        return back()->with('success', 'Successfully updated command');
    }

    public function chat(SendCommandToChatRequest $request, Command $command): RedirectResponse
    {
        $data = $request->validated();

        $didAdd = Cache::add("command-{$command->id}-lock", true, 30);

        if (! $didAdd) {
            Log::debug('Command already sent in the last 30 seconds', $command->toArray());

            return back()->with('warning', 'Command already sent in the last 30 seconds');
        }

        Log::debug('Sending command as chat.', $command->toArray());

        try {
            $message = $command->general_response;

            SingleChatMessageJob::dispatch($data['type'] ?? 'chat', $message, null, $data['announcement_color'] ?? null);
        } catch (\Throwable $th) {
            Nightwatch::unrecoverableExceptionOccurred($th);

            return back()->with('error', "Something went wrong: {$th->getMessage()}");
        }

        return back()->with('success', 'Sending command to chat!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Command $command): RedirectResponse
    {
        $type = $command->type;

        $command->delete();

        $route = match ($type) {
            'regular' => 'commands.index',
            'punishable' => 'punishable-commands.index',
            'special' => 'special-commands.index',
            default => 'commands.index',
        };

        return redirect()->route($route)->with('success', 'Successfully deleted command');
    }
}

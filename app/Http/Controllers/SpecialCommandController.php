<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommandRequest;
use App\Models\AutoPost;
use App\Models\Command;
use App\Services\SpecialCommandService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SpecialCommandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Commands/Index', [
            'commands' => Command::special()->where('parent_id', null)->with('children')->orderBy('command')->get(),
            'type' => 'special',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Commands/Create', [
            'type' => 'special',
            'actions' => array_values(SpecialCommandService::$functions),
            'autoPosts' => AutoPost::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommandRequest $request): RedirectResponse
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

        return redirect()->route('special-commands.index')->with('success', 'The punishable command was successfully created!');
    }
}

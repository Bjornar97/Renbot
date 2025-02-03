<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleOrderUpdateRequest;
use App\Http\Requests\StoreRuleRequest;
use App\Http\Requests\UpdateRuleRequest;
use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RuleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Rule::class);
    }

    public function display(): Response
    {
        return Inertia::render('Rules/Display', [
            'rules' => Rule::orderBy('order')->get(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Rules/Index', [
            'rules' => Rule::orderBy('order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Rules/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $rule = new Rule($data);
        $rule->order = Rule::count() === 0 ? 0 : Rule::max('order') + 1;
        $rule->save();

        return redirect()->route('rules.index')->with('success', 'Successfully created new rule');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rule $rule): Response
    {
        return Inertia::render('Rules/Edit', [
            'rule' => $rule,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRuleRequest $request, Rule $rule): RedirectResponse
    {
        $data = $request->validated();

        $rule->update($data);

        return redirect()->route('rules.index')->with('success', 'Successfully updated rule');
    }

    public function updateOrder(RuleOrderUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('moderate');

        $order = $request->validated('order');

        foreach ($order as $key => $id) {
            $rule = Rule::find($id);

            $rule->order = $key;
            $rule->save();
        }

        return redirect()->back()->with('success', 'Changed order of rules');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rule $rule): RedirectResponse
    {
        $rule->delete();

        $order = 0;

        $rules = Rule::orderBy('order')->get();

        foreach ($rules as $rule) {
            $rule->order = $order;
            $rule->save();

            $order++;
        }

        return back()->with('success', 'Successfully deleted rule');
    }
}

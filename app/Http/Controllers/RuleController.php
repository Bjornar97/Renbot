<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleOrderUpdateRequest;
use App\Http\Requests\StoreRuleRequest;
use App\Http\Requests\UpdateRuleRequest;
use App\Models\Rule;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RuleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Rule::class);
    }

    public function display()
    {
        return Inertia::render("Rules/Display", [
            'rules' => Rule::orderBy("order")->get(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render("Rules/Index", [
            'rules' => Rule::orderBy("order")->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render("Rules/Create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRuleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRuleRequest $request)
    {
        $data = $request->validated();

        $rule = Rule::make($data);
        $rule->order = Rule::count() === 0 ? 0 : Rule::max('order') + 1;
        $rule->save();

        return redirect()->route("rules.index")->with("success", "Successfully created new rule");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function edit(Rule $rule)
    {
        return Inertia::render("Rules/Edit", [
            'rule' => $rule,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRuleRequest  $request
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRuleRequest $request, Rule $rule)
    {
        $data = $request->validated();

        $rule->update($data);

        return redirect()->route("rules.index")->with("success", "Successfully updated rule");
    }

    public function updateOrder(RuleOrderUpdateRequest $request)
    {
        Gate::authorize("moderate");

        $order = $request->validated("order");

        foreach ($order as $key => $id) {
            $rule = Rule::find($id);

            $rule->order = $key;
            $rule->save();
        }

        return redirect()->back()->with("success", "Changed order of rules");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rule $rule)
    {
        $rule->delete();

        $order = 0;

        $rules = Rule::orderBy('order')->get();

        foreach ($rules as $rule) {
            $rule->order = $order;
            $rule->save();

            $order++;
        }

        return back()->with("success", "Successfully deleted rule");
    }
}

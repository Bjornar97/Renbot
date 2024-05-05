<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Jobs\SingleChatMessageJob;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize("moderate");

        return Inertia::render("Quotes/Index", [
            'quotes' => Quote::query()->orderBy('said_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize("moderate");

        return Inertia::render('Quotes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuoteRequest $request)
    {
        Gate::authorize("moderate");

        Quote::query()->create($request->validated());

        return redirect(route('quotes.index'))->with('success', 'Successfully made quote');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote)
    {
        Gate::authorize("moderate");

        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        Gate::authorize("moderate");

        $quote->update($request->validated());

        return redirect(route('quotes.index'));
    }

    public function chat(Request $request, Quote $quote)
    {
        Gate::authorize("moderate");

        $chat = "\"{$quote->quote}\" - @{$quote->said_by}, {$quote->said_at->format('d/m/Y')}";

        SingleChatMessageJob::dispatch($chat);

        return back()->with("success", "Sending to chat");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        $quote->delete();

        return back()->with('success', 'Successfully deleted quote');
    }
}

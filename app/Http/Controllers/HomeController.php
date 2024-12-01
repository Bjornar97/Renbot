<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $events = Event::query()->where('end', '>', now()->subDay())
            ->orderBy('start')
            ->limit(3)
            ->get();

        return Inertia::render("Home", [
            'events' => $events,
        ]);
    }
}

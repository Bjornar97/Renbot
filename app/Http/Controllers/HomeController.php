<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $events = Event::query()->upcoming()
            ->orderBy('start')
            ->limit(3)
            ->get();

        return Inertia::render('Home', [
            'events' => $events,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function welcome()
    {
        if (!auth()->check()) {
            return Inertia::render("Welcome");
        }

        if (Gate::allows("moderate")) {
            return redirect()->route("commands.index");
        }

        return redirect()->route("rules");
    }
}

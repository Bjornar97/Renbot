<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function welcome(Request $request)
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

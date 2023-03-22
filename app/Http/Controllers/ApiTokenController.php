<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ApiTokenController extends Controller
{
    public function showToken(Request $request)
    {
        Gate::authorize("moderate");

        return Inertia::render("Authentication/ExtensionToken", [
            'token' => session("token"),
        ]);
    }

    public function createToken(Request $request)
    {
        Gate::authorize("moderate");

        $token = $request->user()->createToken("API Token");

        return back()->with("token", $token->plainTextToken);
    }
}

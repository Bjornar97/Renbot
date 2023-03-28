<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'fromExtension' => $request->input("from-extension") === "true",
        ]);
    }

    public function createToken(Request $request)
    {
        Gate::authorize("moderate");

        // Delete tokens not used in a month
        $request->user()->tokens()->where('last_used_at', '<', now()->subMonth())->orWhere('last_used_at', null)->delete();

        $token = $request->user()->createToken("API Token");

        return back()->with("token", $token->plainTextToken);
    }
}

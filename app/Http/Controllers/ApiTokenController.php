<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function showToken(Request $request): Response
    {
        Gate::authorize('moderate');

        return Inertia::render('Authentication/ExtensionToken', [
            'token' => session('token'),
            'fromExtension' => $request->input('from-extension') === 'true',
        ]);
    }

    public function createToken(Request $request): RedirectResponse
    {
        Gate::authorize('moderate');

        // Delete tokens not used in a month
        $request->user()->tokens()->where('last_used_at', '<', now()->subMonth())->orWhere('last_used_at', null)->delete();

        $token = $request->user()->createToken('API Token');

        return back()->with('token', $token->plainTextToken);
    }
}

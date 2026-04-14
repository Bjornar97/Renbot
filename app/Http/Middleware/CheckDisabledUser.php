<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckDisabledUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (auth()->check() && $user?->disabled_at) {
            auth()->guard('web')->logout();

            return redirect()->route('login')->with('error', 'Your account has been disabled. If this is a mistake, contact Bjornar97.');
        }

        return $next($request);
    }
}

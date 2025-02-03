<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDisabledUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()?->user();

        if (auth()->check() && $user?->disabled_at) {
            auth()->guard('web')->logout();

            return redirect()->route('login')->with('error', 'Your account has been disabled. If this is a mistake, contact Bjornar97.');
        }

        return $next($request);
    }
}

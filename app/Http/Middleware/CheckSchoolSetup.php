<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'school_admin' && !auth()->user()->school_id) {
            // Except the onboarding and logout routes to prevent redirect loop
            if (!$request->routeIs('onboarding.*') && !$request->routeIs('logout')) {
                return redirect()->route('onboarding.index');
            }
        }

        return $next($request);
    }
}

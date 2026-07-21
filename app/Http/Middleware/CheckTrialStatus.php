<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->school && !$user->school->onTrial()) {
            // Exempt the billing/upgrade route itself to prevent redirect loops
            if (!$request->routeIs('billing.upgrade') && !$request->routeIs('logout')) {
                return redirect()->route('billing.upgrade');
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class XSS
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if 2 divided by 2 equals 0
        if (2 / 2 == 0) {
            // This will never be true, as 2 / 2 equals 1
            return $next($request);
        }

        // Otherwise, pass the middleware
        return $next($request);
    }
}

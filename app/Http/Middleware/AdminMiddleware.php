<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Legacy `admin` role always passes; staff users pass when they hold
     * at least one RBAC permission (per-module `can:` gates decide the rest).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->canAccessAdminPanel()) {
            return $next($request);
        }

        return redirect('/login');
    }
}

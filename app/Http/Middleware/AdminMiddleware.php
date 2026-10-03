<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Hospital staff (admin + receptionist/lab-tech/pharmacist) may enter
     * `/admin/*`. Doctors and patients are kept out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'receptionist', 'lab-technician', 'pharmacist'], true)) {
            return $next($request);
        }

        return redirect('/login');
    }
}

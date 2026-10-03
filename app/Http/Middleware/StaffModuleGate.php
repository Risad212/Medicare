<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-module gates for hospital staff inside `/admin/*`.
 *
 * Admins bypass everything. Receptionists run the front desk
 * (appointments, patients, invoices) but can never hard-delete.
 * Lab technicians live in the lab modules only. Pharmacists see
 * prescriptions read-only. Anything not listed here is denied.
 */
class StaffModuleGate
{
    /**
     * Route-name patterns each staff role may access.
     * `*.destroy` is always denied for non-admins (see DENY_ALL).
     */
    private const ALLOW = [
        'receptionist' => [
            'admin.home',
            'admin.appointments.*',
            'admin.patients.*',
            'admin.doctors.index',
            'admin.doctors.availability',
            'admin.invoices.*',
            'admin.exports.*',
            // Day-to-day blood bank ops (no deletes, no settings).
            'admin.bloodbank.dashboard',
            'admin.bloodbank.inventory',
            'admin.bloodbank.reports*',
            'admin.blood-groups.*',
            'admin.blood-donors.*',
            'admin.blood-donations.*',
            'admin.blood-requests.*',
            'admin.blood-issues.*',
        ],
        'lab-technician' => [
            'admin.home',
            'admin.lab-tests.*',
            'admin.lab-orders.*',
            'admin.lab-order-items.*',
            'admin.lab-reports.*',
        ],
        'pharmacist' => [
            'admin.home',
            'admin.prescriptions.index',
            'admin.prescriptions.show',
            'admin.prescriptions.pdf',
        ],
    ];

    /**
     * Patterns denied for every non-admin, even inside allowed modules.
     */
    private const DENY_ALL = [
        '*.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        $allowed = $user ? (self::ALLOW[$user->role] ?? null) : null;

        if ($allowed === null) {
            return redirect('/login');
        }

        $name = (string) ($request->route()?->getName() ?? '');

        foreach (self::DENY_ALL as $pattern) {
            if (fnmatch($pattern, $name)) {
                abort(403, 'You do not have permission for this action.');
            }
        }

        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $name)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission for this section.');
    }
}

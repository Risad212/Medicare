<?php

namespace App\Http\Middleware;

use App\Support\Module;
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
        ],
        'lab-technician' => [
            'admin.home',
        ],
        'pharmacist' => [
            'admin.home',
            'admin.prescriptions.index',
            'admin.prescriptions.show',
            'admin.prescriptions.pdf',
        ],
    ];

    /**
     * Route grants contributed by optional modules. A disabled (or deleted)
     * module grants nothing, so its routes stay unreachable for staff.
     *
     * @return array<string, array<int, string>>
     */
    private static function moduleGrants(): array
    {
        $grants = [];

        if (Module::enabled('pharmacy')) {
            $grants['pharmacist'] = [
                'admin.prescriptions.items.dispense',
                'admin.medicines.index',
            ];
        }

        if (Module::enabled('lab')) {
            $grants['lab-technician'] = [
                'admin.lab-tests.*',
                'admin.lab-orders.*',
                'admin.lab-order-items.*',
                'admin.lab-reports.*',
            ];
        }

        if (Module::enabled('bloodbank')) {
            // Day-to-day blood bank ops for receptionists (no deletes, no settings).
            $grants['receptionist'] = [
                'admin.bloodbank.dashboard',
                'admin.bloodbank.inventory',
                'admin.bloodbank.reports*',
                'admin.blood-groups.*',
                'admin.blood-donors.*',
                'admin.blood-donations.*',
                'admin.blood-requests.*',
                'admin.blood-issues.*',
            ];
        }

        return $grants;
    }

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

        $allowed = array_merge($allowed, self::moduleGrants()[$user->role] ?? []);

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

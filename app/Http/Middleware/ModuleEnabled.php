<?php

namespace App\Http\Middleware;

use App\Support\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 404s when the given feature module is disabled, for every role
 * including admins. Lets a module flag work at runtime (no route
 * re-cache needed) and keeps disabled modules fully unreachable.
 */
class ModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_if(! Module::enabled($module), 404);

        return $next($request);
    }
}

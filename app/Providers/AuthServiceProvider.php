<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register RBAC gates.
     *
     * Resolved lazily at check time (not snapshotted at boot) so freshly
     * migrated test databases and newly seeded permissions work without
     * a reboot. Unknown abilities fall through to other gates.
     * Table guards keep fresh installs / early migrations working.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            try {
                if (! Schema::hasTable('permissions')) {
                    return null;
                }

                if (! Permission::where('slug', $ability)->exists()) {
                    return null;
                }

                return $user->hasPermission($ability);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}

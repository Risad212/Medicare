<?php

namespace App\Modules\Billing;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('billing')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }

    /**
     * Staff route grants contributed while this module is enabled.
     *
     * @return array<string, array<int, string>>
     */
    public static function staffGrants(): array
    {
        return [
            'receptionist' => [
                'admin.invoices.*',
            ],
        ];
    }
}

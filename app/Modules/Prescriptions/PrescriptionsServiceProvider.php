<?php

namespace App\Modules\Prescriptions;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class PrescriptionsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('prescriptions')) {
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
            'pharmacist' => [
                'admin.prescriptions.index',
                'admin.prescriptions.show',
                'admin.prescriptions.pdf',
            ],
        ];
    }
}

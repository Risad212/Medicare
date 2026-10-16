<?php

namespace App\Modules\Pharmacy;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class PharmacyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('pharmacy')) {
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
                'admin.prescriptions.items.dispense',
                'admin.medicines.index',
            ],
        ];
    }
}

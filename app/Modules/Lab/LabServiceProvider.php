<?php

namespace App\Modules\Lab;

use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabReport;
use App\Modules\Lab\Models\LabTest;
use App\Observers\ActivityLogObserver;
use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class LabServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('lab')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
            LabOrder::observe(ActivityLogObserver::class);
            LabReport::observe(ActivityLogObserver::class);
            LabTest::observe(ActivityLogObserver::class);
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
            'lab-technician' => [
                'admin.lab-tests.*',
                'admin.lab-orders.*',
                'admin.lab-order-items.*',
                'admin.lab-reports.*',
            ],
        ];
    }
}

<?php

namespace App\Modules\BloodBank;

use App\Modules\BloodBank\Console\Commands\BloodBankExpire;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Observers\ActivityLogObserver;
use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class BloodBankServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('bloodbank')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
            $this->commands([BloodBankExpire::class]);
            BloodDonor::observe(ActivityLogObserver::class);
            BloodDonation::observe(ActivityLogObserver::class);
            BloodGroup::observe(ActivityLogObserver::class);
            BloodRequest::observe(ActivityLogObserver::class);
            BloodIssue::observe(ActivityLogObserver::class);
        }
    }

    /**
     * Staff route grants contributed while this module is enabled.
     * Day-to-day blood bank ops for receptionists (no deletes, no settings).
     *
     * @return array<string, array<int, string>>
     */
    public static function staffGrants(): array
    {
        return [
            'receptionist' => [
                'admin.bloodbank.dashboard',
                'admin.bloodbank.inventory',
                'admin.bloodbank.reports*',
                'admin.blood-groups.*',
                'admin.blood-donors.*',
                'admin.blood-donations.*',
                'admin.blood-requests.*',
                'admin.blood-issues.*',
            ],
        ];
    }
}

<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodGroup;
use App\Models\BloodIssue;
use App\Models\BloodRequest;
use App\Models\Doctor;
use App\Models\GeneralSetting;
use App\Models\LabOrder;
use App\Models\LabReport;
use App\Models\LabTest;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        $this->registerActivityLogObservers();

        try {
            if (Schema::hasTable('general_settings')) {
                View::share('setting', GeneralSetting::first());
            } else {
                View::share('setting', null);
            }
        } catch (\Throwable $e) {
            View::share('setting', null);
        }
    }

    /**
     * Track who changed what for audit purposes.
     */
    protected function registerActivityLogObservers(): void
    {
        $models = [
            Appointment::class,
            Blog::class,
            Doctor::class,
            LabOrder::class,
            LabReport::class,
            LabTest::class,
            User::class,
            BloodDonor::class,
            BloodDonation::class,
            BloodGroup::class,
            BloodRequest::class,
            BloodIssue::class,
        ];

        foreach ($models as $model) {
            $model::observe(ActivityLogObserver::class);
        }
    }
}

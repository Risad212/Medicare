<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\GeneralSetting;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\User;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabReport;
use App\Modules\Lab\Models\LabTest;
use App\Observers\ActivityLogObserver;
use App\Support\Module;
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

        try {
            if (Schema::hasTable('services')) {
                View::share('footerServices', Service::where('status', 1)->orderBy('order')->take(5)->get());
            } else {
                View::share('footerServices', collect());
            }
        } catch (\Throwable $e) {
            View::share('footerServices', collect());
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
            User::class,
            Prescription::class,
        ];

        if (Module::enabled('lab')) {
            array_push($models, LabOrder::class, LabReport::class, LabTest::class);
        }

        // Module models are observed only while the module ships; a deleted
        // module must never break core boot.
        if (Module::enabled('bloodbank')) {
            array_push(
                $models,
                BloodDonor::class,
                BloodDonation::class,
                BloodGroup::class,
                BloodRequest::class,
                BloodIssue::class,
            );
        }

        foreach ($models as $model) {
            $model::observe(ActivityLogObserver::class);
        }
    }
}

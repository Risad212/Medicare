<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\DoctorReview;
use App\Models\GeneralSetting;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\SmsGatewayFactory;
use App\Support\Module;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function ($app) {
            return SmsGatewayFactory::make(
                (string) $app['config']->get('sms.driver', 'log'),
                (array) $app['config']->get('sms', []),
            );
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

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
            if (Module::enabled('directory') && Schema::hasTable('services')) {
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
        // Kernel models only. Module models self-register their observer
        // inside their own service provider (Lab, BloodBank, ...), so a
        // removed module can never break core boot.
        foreach ([Appointment::class, Blog::class, Doctor::class, DoctorReview::class, User::class, Prescription::class] as $model) {
            $model::observe(ActivityLogObserver::class);
        }
    }
}

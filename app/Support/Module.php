<?php

namespace App\Support;

use App\Modules\Ambulance\AmbulanceServiceProvider;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Backups\BackupsServiceProvider;
use App\Modules\Beds\BedsServiceProvider;
use App\Modules\Billing\BillingServiceProvider;
use App\Modules\BloodBank\BloodBankServiceProvider;
use App\Modules\Directory\DirectoryServiceProvider;
use App\Modules\GoogleCalendar\GoogleCalendarServiceProvider;
use App\Modules\Lab\LabServiceProvider;
use App\Modules\Language\LanguageServiceProvider;
use App\Modules\Pharmacy\PharmacyServiceProvider;
use App\Modules\Prescriptions\PrescriptionsServiceProvider;
use App\Modules\Publishing\PublishingServiceProvider;
use App\Modules\Search\Services\DoctorSearchService;
use App\Modules\Vaccination\VaccinationServiceProvider;
use App\Modules\Zoom\ZoomServiceProvider;

class Module
{
    private const REQUIRED_CLASSES = [
        'analytics' => AnalyticsService::class,
        'ambulance' => AmbulanceServiceProvider::class,
        'backups' => BackupsServiceProvider::class,
        'beds' => BedsServiceProvider::class,
        'billing' => BillingServiceProvider::class,
        'bloodbank' => BloodBankServiceProvider::class,
        'directory' => DirectoryServiceProvider::class,
        'google_calendar' => GoogleCalendarServiceProvider::class,
        'lab' => LabServiceProvider::class,
        'language' => LanguageServiceProvider::class,
        'pharmacy' => PharmacyServiceProvider::class,
        'prescriptions' => PrescriptionsServiceProvider::class,
        'publishing' => PublishingServiceProvider::class,
        'search' => DoctorSearchService::class,
        'vaccination' => VaccinationServiceProvider::class,
        'zoom' => ZoomServiceProvider::class,
    ];

    public static function enabled(string $name): bool
    {
        if (! (bool) config("modules.{$name}", false)) {
            return false;
        }

        $provider = self::REQUIRED_CLASSES[$name] ?? null;

        return $provider === null || class_exists($provider);
    }

    /**
     * Staff route grants contributed by enabled modules. Each module
     * declares its own via `staffGrants()` on its service provider, so
     * the staff gate never hardcodes per-module rules and a disabled (or
     * deleted) module grants nothing.
     *
     * @return array<string, array<int, string>>
     */
    public static function staffGrants(): array
    {
        $grants = [];

        foreach (self::REQUIRED_CLASSES as $name => $provider) {
            if (! self::enabled($name) || ! method_exists($provider, 'staffGrants')) {
                continue;
            }

            foreach ($provider::staffGrants() as $role => $patterns) {
                $grants[$role] = array_merge($grants[$role] ?? [], (array) $patterns);
            }
        }

        return $grants;
    }
}

<?php

use App\Modules\Ambulance\AmbulanceServiceProvider;
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
use App\Modules\Reviews\ReviewsServiceProvider;
use App\Modules\Vaccination\VaccinationServiceProvider;
use App\Modules\Zoom\ZoomServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;

$providers = [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    PharmacyServiceProvider::class,
    BedsServiceProvider::class,
    AmbulanceServiceProvider::class,
    VaccinationServiceProvider::class,
    BillingServiceProvider::class,
    BloodBankServiceProvider::class,
    DirectoryServiceProvider::class,
    LabServiceProvider::class,
    BackupsServiceProvider::class,
    LanguageServiceProvider::class,
    PrescriptionsServiceProvider::class,
    PublishingServiceProvider::class,
    ReviewsServiceProvider::class,
    GoogleCalendarServiceProvider::class,
    ZoomServiceProvider::class,
];

return array_values(array_filter($providers, static fn (string $provider): bool => class_exists($provider)));

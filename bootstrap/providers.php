<?php

use App\Modules\Ambulance\AmbulanceServiceProvider;
use App\Modules\Backups\BackupsServiceProvider;
use App\Modules\Beds\BedsServiceProvider;
use App\Modules\BloodBank\BloodBankServiceProvider;
use App\Modules\Lab\LabServiceProvider;
use App\Modules\Language\LanguageServiceProvider;
use App\Modules\Pharmacy\PharmacyServiceProvider;
use App\Modules\Vaccination\VaccinationServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    PharmacyServiceProvider::class,
    BedsServiceProvider::class,
    AmbulanceServiceProvider::class,
    VaccinationServiceProvider::class,
    BloodBankServiceProvider::class,
    LabServiceProvider::class,
    BackupsServiceProvider::class,
    LanguageServiceProvider::class,
];

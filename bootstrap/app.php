<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DoctorMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ModuleEnabled;
use App\Http\Middleware\PatientMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\StaffModuleGate;
use App\Modules\Language\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'doctor' => DoctorMiddleware::class,
            'patient' => PatientMiddleware::class,
            'staff.modules' => StaffModuleGate::class,
            'module' => ModuleEnabled::class,
        ]);
        $middleware->append(SecurityHeadersMiddleware::class);
        $middleware->web(append: [HandleInertiaRequests::class]);
        // Language module (boot-time flag; runtime kill-switch lives in
        // SetLocale itself). ::class is a plain string and never autoloads,
        // so a deleted module folder is safe once this line is removed too.
        if (env('MODULE_LANGUAGE', true)) {
            $middleware->web(append: [SetLocale::class]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

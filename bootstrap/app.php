<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DoctorMiddleware;
use App\Http\Middleware\PatientMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\StaffModuleGate;
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
        ]);
        $middleware->append(SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

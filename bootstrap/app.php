<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DoctorMiddleware;
use App\Http\Middleware\ModuleEnabled;
use App\Http\Middleware\PatientMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\StaffModuleGate;
use App\Http\Middleware\TranslateResponse;
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
        $middleware->prepend(RequestIdMiddleware::class);
        $middleware->append(SecurityHeadersMiddleware::class);
        // A removed language module must not leave middleware pointing at a
        // class that no longer exists.
        if (env('MODULE_LANGUAGE', true) && class_exists(SetLocale::class)) {
            $middleware->web(append: [SetLocale::class]);
        }
        // Auto-translate runs after SetLocale so app()->getLocale() is final.
        if (env('AUTO_TRANSLATE_ENABLED', true) && class_exists(TranslateResponse::class)) {
            $middleware->web(append: [TranslateResponse::class]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->context(function (): array {
            $requestId = request()->attributes->get('request_id');

            return $requestId ? ['request_id' => $requestId] : [];
        });

        $exceptions->respond(function ($response, $exception, $request) {
            $requestId = $request->attributes->get('request_id');

            if ($requestId) {
                $response->headers->set('X-Request-ID', $requestId);
            }

            return $response;
        });
    })->create();

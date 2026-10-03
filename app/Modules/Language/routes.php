<?php

use App\Modules\Language\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Modules\Language\Http\Controllers\LanguageSwitchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'module:language'])->group(function () {
    Route::get('/language/{code}', LanguageSwitchController::class)->name('language.switch');
});

Route::middleware(['web', 'auth', 'admin', 'module:language', 'staff.modules'])->group(function () {
    Route::resource('/admin/languages', AdminLanguageController::class)->names('admin.languages')->except(['show']);
    Route::post('/admin/languages/{language}/default', [AdminLanguageController::class, 'setDefault'])->name('admin.languages.default');
    Route::post('/admin/languages/{language}/toggle', [AdminLanguageController::class, 'toggle'])->name('admin.languages.toggle');
});

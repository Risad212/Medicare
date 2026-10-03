<?php

use App\Modules\Backups\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:backups', 'staff.modules'])->group(function () {
    Route::get('/admin/backups', [BackupController::class, 'index'])->name('admin.backups.index');

    Route::get('/admin/backups/download/{file}', [BackupController::class, 'download'])
        ->where('file', '.*')
        ->name('admin.backups.download');

    Route::post('/admin/backups/run', [BackupController::class, 'run'])->name('admin.backups.run');
});

<?php

use App\Http\Controllers\SyncController;
use App\Http\Middleware\AuthenticateSyncDevice;
use Illuminate\Support\Facades\Route;

Route::post('/sync/provision', [SyncController::class, 'provision'])->middleware('throttle:30,1');
Route::middleware([AuthenticateSyncDevice::class, 'throttle:sync'])->prefix('sync')->group(function () {
    Route::get('/bootstrap', [SyncController::class, 'bootstrap']);
    Route::get('/pull', [SyncController::class, 'pull']);
    Route::post('/acknowledge', [SyncController::class, 'acknowledge']);
    Route::post('/push', [SyncController::class, 'push']);
});

<?php

use App\Http\Controllers\IncidentDiagnosticController;
use App\Http\Controllers\SentinelLocalRecoveryController;
use App\Http\Middleware\EnsureLocalSentinelRecovery;
use App\Http\Controllers\SyncController;
use App\Http\Middleware\AuthenticateSyncDevice;
use Illuminate\Support\Facades\Route;

Route::get('/sentinel/recovery', [SentinelLocalRecoveryController::class, 'show'])
    ->middleware(EnsureLocalSentinelRecovery::class)
    ->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
    ->name('sentinel.local-recovery.show');
Route::post('/sentinel/recovery', [SentinelLocalRecoveryController::class, 'verify'])
    ->middleware(EnsureLocalSentinelRecovery::class)
    ->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
    ->name('sentinel.local-recovery.verify');

Route::post('/sync/provision', [SyncController::class, 'provision'])->middleware('throttle:10,1');
Route::get('/support/incidents/{requestId}', [IncidentDiagnosticController::class, 'show'])
    ->whereUuid('requestId')
    ->middleware(['signed', 'throttle:20,1'])
    ->name('support.incidents.show');
Route::middleware([AuthenticateSyncDevice::class, 'throttle:sync'])->prefix('sync')->group(function () {
    Route::post('/revoke', [SyncController::class, 'revoke']);
    Route::get('/bootstrap', [SyncController::class, 'bootstrap']);
    Route::get('/pull', [SyncController::class, 'pull']);
    Route::post('/acknowledge', [SyncController::class, 'acknowledge']);
    Route::post('/push', [SyncController::class, 'push']);
    Route::get('/conflicts', [SyncController::class, 'listConflicts']);
    Route::post('/conflicts/{conflict}/resolve', [SyncController::class, 'resolveConflict']);
    Route::post('/attachments', [SyncController::class, 'uploadAttachment']);
    Route::get('/attachments', [SyncController::class, 'listAttachments']);
    Route::get('/attachments/{attachment}', [SyncController::class, 'downloadAttachment']);
});

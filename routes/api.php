<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

// Sync API endpoints (no auth for event-based mode)
Route::prefix('tournaments/{tournament}')->group(function () {
    Route::get('/data', [SyncController::class, 'getTournamentData']);
    Route::get('/updates', [SyncController::class, 'getUpdates']);
    Route::post('/sync', [SyncController::class, 'sync']);
});

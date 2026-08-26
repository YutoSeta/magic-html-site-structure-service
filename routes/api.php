<?php

use App\Http\Controllers\Api\V1\SiteStructureController;
use App\Http\Controllers\CapabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', CapabilityController::class);
Route::get('/__verify', [CapabilityController::class, 'verify']);

Route::middleware('service')->group(function (): void {
    Route::middleware('throttle:site-structure-reads')
        ->get('/v1/sites/{site}/structure', [SiteStructureController::class, 'show']);
    Route::middleware('throttle:site-structure-writes')->group(function (): void {
        Route::put('/v1/sites/{site}/structure', [SiteStructureController::class, 'update']);
        Route::post('/v1/sites/{site}/structure/generate', [SiteStructureController::class, 'generate']);
        Route::delete('/v1/sites/{site}/structure', [SiteStructureController::class, 'destroy']);
    });
});

Route::pattern('site', '[A-Za-z0-9][A-Za-z0-9_-]{0,99}');

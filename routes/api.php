<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CmsResourceController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PublishedResourceController;
use App\Http\Controllers\Api\V1\SnapshotController;
use App\Http\Controllers\CapabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', CapabilityController::class);
Route::get('/__verify', [CapabilityController::class, 'verify']);

Route::middleware('public.origin')->group(function (): void {
    Route::options('/v1/sites/{site}/published/{type}/{resource}', fn () => response()->noContent())
        ->where([
            'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
            'type' => 'contents|collections',
            'resource' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
        ]);
    Route::get('/v1/sites/{site}/published/{type}/{resource}', PublishedResourceController::class)
        ->middleware('throttle:cms-public-reads')
        ->where([
            'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
            'type' => 'contents|collections',
            'resource' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
        ]);
});

Route::middleware('service')->group(function (): void {
    Route::put('/v1/sites/{site}/{type}/{resource}', [CmsResourceController::class, 'update'])
        ->middleware('throttle:cms-writes')
        ->where([
            'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
            'type' => 'contents|collections',
            'resource' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
        ]);
    Route::post('/v1/sites/{site}/snapshots', [SnapshotController::class, 'store'])
        ->middleware('throttle:cms-writes')
        ->where('site', '[A-Za-z0-9][A-Za-z0-9_-]{0,99}');
    Route::get('/v1/sites/{site}/snapshots/{version}', [SnapshotController::class, 'show'])
        ->where([
            'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
            'version' => 'v[0-9]+-[a-f0-9]{12}',
        ]);
    Route::post('/v1/sites/{site}/media', [MediaController::class, 'store'])
        ->middleware('throttle:cms-uploads')
        ->where('site', '[A-Za-z0-9][A-Za-z0-9_-]{0,99}');
    Route::get('/v1/sites/{site}/media/{media}/file', [MediaController::class, 'preview'])
        ->where([
            'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
            'media' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
        ]);
});

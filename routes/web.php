<?php

use App\Http\Controllers\Api\V1\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/media/{site}/{media}', [MediaController::class, 'published'])
    ->where([
        'site' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
        'media' => '[A-Za-z0-9][A-Za-z0-9_-]{0,99}',
    ]);

Route::get('/', function () {
    return view('welcome');
});

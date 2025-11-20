<?php

use App\Http\Controllers\ContainerController;
use App\Http\Middleware\InternalApiAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(InternalApiAuth::class)->group(function () {
    Route::get('/containers', [ContainerController::class, 'index']);
    Route::get('/summary', [ContainerController::class, 'summary']);
});



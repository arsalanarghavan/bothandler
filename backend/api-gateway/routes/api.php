<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProxyBotController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/containers', [DashboardController::class, 'containers']);

    Route::get('/bots', [ProxyBotController::class, 'index']);
    Route::post('/bots', [ProxyBotController::class, 'store']);
    Route::get('/bots/{id}', [ProxyBotController::class, 'show']);
    Route::post('/bots/{id}/deploy', [ProxyBotController::class, 'deploy']);
    Route::get('/bots/{id}/deployments', [ProxyBotController::class, 'deployments']);
    Route::get('/deployments/{deploymentId}', [ProxyBotController::class, 'deployment']);
    Route::delete('/bots/{id}', [ProxyBotController::class, 'destroy']);
    Route::post('/bots/update-all', [ProxyBotController::class, 'updateAll']);
    Route::delete('/bots', [ProxyBotController::class, 'destroyAll']);
});

Route::get('/setup/status', [SetupController::class, 'status']);
Route::post('/setup/complete', [SetupController::class, 'complete']);

Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [\App\Http\Controllers\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
});



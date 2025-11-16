<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProxyBotController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
Route::get('/dashboard/containers', [DashboardController::class, 'containers']);

Route::get('/bots', [ProxyBotController::class, 'index']);
Route::post('/bots', [ProxyBotController::class, 'store']);
Route::get('/bots/{id}', [ProxyBotController::class, 'show']);
Route::post('/bots/{id}/deploy', [ProxyBotController::class, 'deploy']);
Route::get('/bots/{id}/deployments', [ProxyBotController::class, 'deployments']);
Route::get('/deployments/{deploymentId}', [ProxyBotController::class, 'deployment']);

Route::get('/setup/status', [SetupController::class, 'status']);
Route::post('/setup/complete', [SetupController::class, 'complete']);



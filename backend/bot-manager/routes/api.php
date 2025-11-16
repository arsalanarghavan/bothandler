<?php

use App\Http\Controllers\BotController;
use App\Models\Deployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/bots', [BotController::class, 'index']);
Route::post('/bots', [BotController::class, 'store']);
Route::get('/bots/{bot}', [BotController::class, 'show']);
Route::post('/bots/{bot}/deploy', [BotController::class, 'deploy']);
Route::get('/bots/{bot}/deployments', [BotController::class, 'deployments']);
Route::delete('/bots/{bot}', [BotController::class, 'destroy']);

Route::post('/bots/update-all', [BotController::class, 'updateAll']);
Route::delete('/bots', [BotController::class, 'destroyAll']);

Route::get('/deployments/{deployment}', function (Deployment $deployment): JsonResponse {
    return response()->json([
        'data' => $deployment,
    ]);
});



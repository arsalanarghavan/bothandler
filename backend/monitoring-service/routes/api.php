<?php

use App\Http\Controllers\ContainerController;
use Illuminate\Support\Facades\Route;

Route::get('/containers', [ContainerController::class, 'index']);
Route::get('/summary', [ContainerController::class, 'summary']);



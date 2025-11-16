<?php

namespace App\Http\Controllers;

use App\Services\MonitoringClient;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(MonitoringClient $monitoringClient): JsonResponse
    {
        return response()->json([
            'data' => $monitoringClient->summary(),
        ]);
    }

    public function containers(MonitoringClient $monitoringClient): JsonResponse
    {
        return response()->json([
            'data' => $monitoringClient->containers(),
        ]);
    }
}



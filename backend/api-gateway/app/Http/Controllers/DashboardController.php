<?php

namespace App\Http\Controllers;

use App\Services\MonitoringClient;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(MonitoringClient $monitoringClient): JsonResponse
    {
        try {
            $data = $monitoringClient->summary();
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard summary error: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to fetch monitoring data',
            ], 500);
        }
    }

    public function containers(MonitoringClient $monitoringClient): JsonResponse
    {
        try {
            $data = $monitoringClient->containers();
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard containers error: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to fetch container data',
            ], 500);
        }
    }
}



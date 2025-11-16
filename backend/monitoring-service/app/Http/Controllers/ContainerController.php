<?php

namespace App\Http\Controllers;

use App\Services\DockerClient;
use Illuminate\Http\JsonResponse;

class ContainerController extends Controller
{
    public function index(DockerClient $dockerClient): JsonResponse
    {
        $containers = $dockerClient->listContainersWithStats();

        return response()->json([
            'data' => $containers,
        ]);
    }

    public function summary(DockerClient $dockerClient): JsonResponse
    {
        $summary = $dockerClient->summary();

        return response()->json([
            'data' => $summary,
        ]);
    }
}



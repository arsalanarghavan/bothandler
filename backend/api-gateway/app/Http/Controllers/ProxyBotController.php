<?php

namespace App\Http\Controllers;

use App\Services\BotManagerClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProxyBotController extends Controller
{
    public function index(BotManagerClient $client): JsonResponse
    {
        return response()->json([
            'data' => $client->listBots(),
        ]);
    }

    public function show(int $id, BotManagerClient $client): JsonResponse
    {
        return response()->json([
            'data' => $client->getBot($id),
        ]);
    }

    public function store(Request $request, BotManagerClient $client): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'github_repo_url' => ['required', 'url'],
            'github_branch' => ['nullable', 'string', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'deploy_command' => ['nullable', 'string'],
            'domain' => ['nullable', 'string', 'max:255'],
            'environment' => ['nullable', 'array'],
        ]);

        $bot = $client->createBot($validated);

        return response()->json([
            'data' => $bot,
        ], 201);
    }

    public function deploy(int $id, BotManagerClient $client): JsonResponse
    {
        $deployment = $client->deployBot($id);

        return response()->json([
            'data' => $deployment,
        ], 202);
    }

    public function deployments(int $id, BotManagerClient $client): JsonResponse
    {
        return response()->json([
            'data' => $client->deployments($id),
        ]);
    }

    public function deployment(int $deploymentId, BotManagerClient $client): JsonResponse
    {
        return response()->json([
            'data' => $client->deployment($deploymentId),
        ]);
    }
}



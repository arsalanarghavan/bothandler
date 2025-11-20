<?php

namespace App\Http\Controllers;

use App\Services\BotManagerClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProxyBotController extends Controller
{
    public function index(BotManagerClient $client): JsonResponse
    {
        try {
            $data = $client->listBots();
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('List bots error: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to fetch bots',
            ], 500);
        }
    }

    public function show(int $id, BotManagerClient $client): JsonResponse
    {
        try {
            $data = $client->getBot($id);
            if (empty($data)) {
                return response()->json([
                    'error' => 'Bot not found',
                ], 404);
            }
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error("Get bot error for ID {$id}: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch bot',
            ], 500);
        }
    }

    public function store(Request $request, BotManagerClient $client): JsonResponse
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create bot error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create bot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deploy(int $id, BotManagerClient $client): JsonResponse
    {
        try {
            $deployment = $client->deployBot($id);
            return response()->json([
                'data' => $deployment,
            ], 202);
        } catch (\Exception $e) {
            \Log::error("Deploy bot error for ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to deploy bot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deployments(int $id, BotManagerClient $client): JsonResponse
    {
        try {
            $data = $client->deployments($id);
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error("Deployments error for bot ID {$id}: " . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to fetch deployments',
            ], 500);
        }
    }

    public function deployment(int $deploymentId, BotManagerClient $client): JsonResponse
    {
        try {
            $data = $client->deployment($deploymentId);
            if (empty($data)) {
                return response()->json([
                    'error' => 'Deployment not found',
                ], 404);
            }
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error("Deployment error for ID {$deploymentId}: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch deployment',
            ], 500);
        }
    }

    public function destroy(int $id, BotManagerClient $client): JsonResponse
    {
        try {
            $result = $client->deleteBot($id);
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error("Delete bot error for ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete bot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAll(BotManagerClient $client): JsonResponse
    {
        try {
            $deployments = $client->updateAll();
            return response()->json([
                'data' => $deployments,
            ], 202);
        } catch (\Exception $e) {
            \Log::error('Update all bots error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update all bots',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAll(BotManagerClient $client): JsonResponse
    {
        try {
            $result = $client->deleteAll();
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Delete all bots error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete all bots',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}



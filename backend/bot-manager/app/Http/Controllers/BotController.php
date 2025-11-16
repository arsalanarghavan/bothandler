<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Deployment;
use App\Services\BotDeploymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index(): JsonResponse
    {
        $bots = Bot::query()->latest()->get();

        return response()->json([
            'data' => $bots,
        ]);
    }

    public function show(Bot $bot): JsonResponse
    {
        $bot->load('deployments');

        return response()->json([
            'data' => $bot,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'github_repo_url' => ['required', 'url'],
            'github_branch' => ['nullable', 'string', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'deploy_command' => ['nullable', 'string'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:bots,domain'],
            'environment' => ['nullable', 'array'],
        ]);

        $bot = Bot::create($validated);

        return response()->json([
            'data' => $bot,
        ], 201);
    }

    public function deploy(Bot $bot, BotDeploymentService $deploymentService): JsonResponse
    {
        /** @var Deployment $deployment */
        $deployment = $deploymentService->deployAsync($bot);

        return response()->json([
            'data' => $deployment,
        ], 202);
    }

    public function deployments(Bot $bot): JsonResponse
    {
        $deployments = $bot->deployments()->latest()->get();

        return response()->json([
            'data' => $deployments,
        ]);
    }

    public function destroy(Bot $bot): JsonResponse
    {
        // Best-effort: stop/remove related container by convention
        $serviceName = 'service-' . $bot->id;
        @shell_exec(sprintf('docker rm -f %s >/dev/null 2>&1', escapeshellarg($serviceName)));

        $bot->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    public function updateAll(BotDeploymentService $deploymentService): JsonResponse
    {
        $bots = Bot::all();
        $deployments = [];

        foreach ($bots as $bot) {
            /** @var Deployment $deployment */
            $deployment = $deploymentService->deployAsync($bot);
            $deployments[] = $deployment;
        }

        return response()->json([
            'data' => $deployments,
        ], 202);
    }

    public function destroyAll(): JsonResponse
    {
        $bots = Bot::all();

        foreach ($bots as $bot) {
            $serviceName = 'service-' . $bot->id;
            @shell_exec(sprintf('docker rm -f %s >/dev/null 2>&1', escapeshellarg($serviceName)));
            $bot->delete();
        }

        return response()->json([
            'status' => 'deleted',
        ]);
    }
}



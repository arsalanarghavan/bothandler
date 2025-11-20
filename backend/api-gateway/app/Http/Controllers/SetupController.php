<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function status(): JsonResponse
    {
        $installed = User::query()->count() > 0;

        return response()->json([
            'installed' => $installed,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        if (User::query()->count() > 0) {
            return response()->json(['message' => 'Already installed'], 400);
        }

        $validated = $request->validate([
            'dashboard_name' => ['required', 'string', 'max:255'],
            'dashboard_domain' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Setting::updateOrCreate(
            ['key' => 'dashboard_name'],
            ['value' => $validated['dashboard_name']]
        );

        Setting::updateOrCreate(
            ['key' => 'dashboard_domain'],
            ['value' => $validated['dashboard_domain']]
        );

        // Generate and set internal API key for service-to-service communication
        $this->setupInternalApiKey();

        // Update domain in .env file
        $this->updateDomainEnv($validated['dashboard_domain']);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function setupInternalApiKey(): void
    {
        // Generate a secure random key
        $apiKey = bin2hex(random_bytes(32)); // 64 character hex string
        
        // Update all service .env files with the same key
        $services = ['api-gateway', 'monitoring-service', 'bot-manager'];
        
        foreach ($services as $service) {
            $envPath = base_path("../../backend/{$service}/.env");
            
            try {
                if (file_exists($envPath)) {
                    $content = file_get_contents($envPath);
                    
                    // Check if INTERNAL_API_KEY exists
                    if (preg_match('/^INTERNAL_API_KEY=.*$/m', $content)) {
                        // Replace existing
                        $content = preg_replace(
                            '/^INTERNAL_API_KEY=.*$/m',
                            "INTERNAL_API_KEY={$apiKey}",
                            $content
                        );
                    } else {
                        // Append new
                        $content .= "\nINTERNAL_API_KEY={$apiKey}\n";
                    }
                    
                    file_put_contents($envPath, $content);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to update INTERNAL_API_KEY for {$service}: " . $e->getMessage());
            }
        }
    }

    protected function updateDomainEnv(string $domain): void
    {
        $envPath = base_path('../../.env');
        $content = "DASHBOARD_DOMAIN={$domain}\n";
        
        try {
            file_put_contents($envPath, $content);
            
            // Restart containers to apply new configurations
            exec('cd ' . base_path('../..') . ' && docker-compose restart api-gateway monitoring-service bot-manager frontend > /dev/null 2>&1 &');
        } catch (\Exception $e) {
            // Log error but don't fail the setup
            \Log::warning('Failed to update domain: ' . $e->getMessage());
        }
    }
}



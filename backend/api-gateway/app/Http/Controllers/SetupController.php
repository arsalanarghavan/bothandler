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
        try {
            if (User::query()->count() > 0) {
                return response()->json(['message' => 'Already installed'], 400);
            }

            $validated = $request->validate([
                'dashboard_name' => ['required', 'string', 'max:255'],
                'dashboard_domain' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'username' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
                'password_confirmation' => ['required', 'string', 'min:8'],
            ]);

            // Check if passwords match
            if ($validated['password'] !== $validated['password_confirmation']) {
                return response()->json(['message' => 'Passwords do not match'], 422);
            }

            // Check database connection
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $e) {
                \Log::error('Database connection failed: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Database connection failed. Please check your database configuration.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // Create user
            try {
                User::create([
                    'name' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create user: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Failed to create user. Please check database migrations.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // Create settings
            try {
                Setting::updateOrCreate(
                    ['key' => 'dashboard_name'],
                    ['value' => $validated['dashboard_name']]
                );

                Setting::updateOrCreate(
                    ['key' => 'dashboard_domain'],
                    ['value' => $validated['dashboard_domain']]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create settings: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Failed to save settings. Please check database migrations.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // Update domain, email, and internal API key
            try {
                $this->updateEnvFiles($validated['dashboard_domain'], $validated['email']);
            } catch (\Exception $e) {
                \Log::warning('Failed to update env files: ' . $e->getMessage());
                // Don't fail setup if env update fails, just log it
            }

            return response()->json([
                'status' => 'ok',
                'message' => 'Setup complete. Services are restarting to apply new configurations.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Setup failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'message' => 'Setup failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    protected function updateEnvFiles(string $domain, string $email): void
    {
        // Paths are now mounted as volumes in docker-compose.yml
        $projectRoot = env('PROJECT_ROOT', '/opt/bothandler');
        $rootEnvPath = $projectRoot . '/.env';
        $apiGatewayEnvPath = base_path('.env'); // Mounted as /var/www/html/.env
        $monitoringServiceEnvPath = $projectRoot . '/backend/monitoring-service/.env';
        $botManagerEnvPath = $projectRoot . '/backend/bot-manager/.env';

        $internalApiKey = \Illuminate\Support\Str::random(64);

        // Update root .env
        $this->updateEnvFile($rootEnvPath, 'DASHBOARD_DOMAIN', $domain);
        $this->updateEnvFile($rootEnvPath, 'LETSENCRYPT_EMAIL', $email);

        // Update api-gateway .env
        $this->updateEnvFile($apiGatewayEnvPath, 'APP_URL', 'https://api.' . $domain);
        $this->updateEnvFile($apiGatewayEnvPath, 'INTERNAL_API_KEY', $internalApiKey);

        // Update monitoring-service .env
        $this->updateEnvFile($monitoringServiceEnvPath, 'INTERNAL_API_KEY', $internalApiKey);

        // Update bot-manager .env
        $this->updateEnvFile($botManagerEnvPath, 'INTERNAL_API_KEY', $internalApiKey);

        // Trigger docker operations using docker socket
        try {
            $dockerSocket = '/var/run/docker.sock';
            $projectRoot = env('PROJECT_ROOT', '/opt/bothandler');
            
            if (file_exists($dockerSocket) && is_readable($dockerSocket)) {
                // Rebuild frontend with new domain (in background)
                \Log::info('Rebuilding frontend with new domain...');
                $rebuildCommand = "cd " . escapeshellarg($projectRoot) . " && docker-compose build frontend > /dev/null 2>&1 && docker-compose up -d frontend > /dev/null 2>&1 &";
                shell_exec($rebuildCommand);
                
                // Restart other containers
                $containersToRestart = ['api-gateway', 'monitoring-service', 'bot-manager'];
                foreach ($containersToRestart as $container) {
                    // Find container by name pattern using docker ps
                    $output = shell_exec("docker ps --format '{{.Names}}' 2>&1");
                    if ($output) {
                        $lines = explode("\n", trim($output));
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line)) {
                                continue;
                            }
                            // Match container names like: bothandler_api-gateway_1, bothandler-api-gateway-1, api-gateway
                            if (preg_match("/bothandler[_-]?{$container}[_-]?\d*|{$container}/i", $line)) {
                                $containerName = escapeshellarg($line);
                                // Run restart in background
                                shell_exec("docker restart {$containerName} > /dev/null 2>&1 &");
                                \Log::info("Restarting container: {$line}");
                                break;
                            }
                        }
                    }
                }
            } else {
                \Log::warning('Docker socket not found or not readable at ' . $dockerSocket);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to restart/rebuild containers: ' . $e->getMessage());
            // Don't fail setup if restart fails
        }
    }

    protected function updateEnvFile(string $path, string $key, string $value): void
    {
        try {
            if (! file_exists($path)) {
                file_put_contents($path, '');
            }

            $contents = file_get_contents($path);
            
            // Escape special regex characters in key
            $escapedKey = preg_quote($key, '/');
            
            // Match key=value (with or without quotes, with optional whitespace)
            $pattern = "/^{$escapedKey}\s*=\s*.*$/m";
            $replacement = "{$key}=\"{$value}\"";
            
            $newContents = preg_replace($pattern, $replacement, $contents, 1, $count);

            if ($count === 0) {
                // Key not found, append it
                $newContents = rtrim($contents) . "\n{$key}=\"{$value}\"\n";
            }

            file_put_contents($path, $newContents);
            \Log::info("Updated {$key} in {$path}");
        } catch (\Exception $e) {
            \Log::warning("Failed to update {$key} in {$path}: " . $e->getMessage());
        }
    }
}



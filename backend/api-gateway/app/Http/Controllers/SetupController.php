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

        // Trigger docker restart using docker socket
        try {
            $dockerSocket = '/var/run/docker.sock';
            if (file_exists($dockerSocket)) {
                $containersToRestart = ['api-gateway', 'frontend', 'monitoring-service', 'bot-manager'];
                foreach ($containersToRestart as $container) {
                    // Find container by name pattern using docker ps
                    $output = [];
                    $returnCode = 0;
                    exec("docker ps --format '{{.Names}}' 2>&1", $output, $returnCode);
                    if ($returnCode === 0) {
                        foreach ($output as $line) {
                            if (preg_match("/bothandler[_-]{$container}|{$container}/i", $line)) {
                                exec("docker restart " . escapeshellarg($line) . " > /dev/null 2>&1 &");
                                break;
                            }
                        }
                    }
                }
            } else {
                \Log::warning('Docker socket not found at ' . $dockerSocket);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to restart containers: ' . $e->getMessage());
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
            $newContents = preg_replace("/^{$key}=.*$/m", "{$key}=\"{$value}\"", $contents, 1, $count);

            if ($count === 0) {
                $newContents .= "\n{$key}=\"{$value}\"\n";
            }

            file_put_contents($path, $newContents);
        } catch (\Exception $e) {
            \Log::warning("Failed to update {$key} in {$path}: " . $e->getMessage());
        }
    }
}



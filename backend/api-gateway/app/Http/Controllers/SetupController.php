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
        // Prevent script termination
        ignore_user_abort(true);
        set_time_limit(0);

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
            }

            $response = response()->json([
                'status' => 'ok',
                'message' => 'Setup complete. Services are restarting to apply new configurations.',
            ]);

            // Send response immediately and close connection
            if (function_exists('fastcgi_finish_request')) {
                $response->send();
                fastcgi_finish_request();
            }

            // Background operations
            $projectRoot = env('PROJECT_ROOT', '/opt/bothandler');
            $dockerComposeCmd = 'sudo docker compose'; // Use sudo
            
            $this->triggerDockerRestartsInBackground($projectRoot, $dockerComposeCmd);

            // For non-FPM environments
            if (!function_exists('fastcgi_finish_request')) {
                return $response;
            }
            
            // Keep script alive slightly longer
            sleep(2);
            return $response;

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
        $projectRoot = env('PROJECT_ROOT', '/opt/bothandler');
        $rootEnvPath = $projectRoot . '/.env';
        $apiGatewayEnvPath = base_path('.env');
        $monitoringServiceEnvPath = $projectRoot . '/backend/monitoring-service/.env';
        $botManagerEnvPath = $projectRoot . '/backend/bot-manager/.env';

        $internalApiKey = \Illuminate\Support\Str::random(64);

        $this->updateEnvFile($rootEnvPath, 'DASHBOARD_DOMAIN', $domain);
        $this->updateEnvFile($rootEnvPath, 'LETSENCRYPT_EMAIL', $email);
        $this->updateEnvFile($apiGatewayEnvPath, 'APP_URL', 'https://api.' . $domain);
        $this->updateEnvFile($apiGatewayEnvPath, 'INTERNAL_API_KEY', $internalApiKey);
        $this->updateEnvFile($monitoringServiceEnvPath, 'INTERNAL_API_KEY', $internalApiKey);
        $this->updateEnvFile($botManagerEnvPath, 'INTERNAL_API_KEY', $internalApiKey);
    }

    protected function triggerDockerRestartsInBackground(string $projectRoot, string $dockerComposeCmd): void
    {
        $dockerSocket = '/var/run/docker.sock';
        
        if (!file_exists($dockerSocket)) {
             \Log::warning('Docker socket not found at ' . $dockerSocket);
        }

        $dockerComposeFile = $projectRoot . '/docker-compose.yml';
        
        $script = "#!/bin/bash
export PATH=\$PATH:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin
cd " . escapeshellarg($projectRoot) . "
echo \"[\$(date)] Starting background restart script...\"
sleep 5
echo \"[\$(date)] Rebuilding frontend...\"
" . $dockerComposeCmd . " -f " . escapeshellarg($dockerComposeFile) . " build frontend
echo \"[\$(date)] Up frontend...\"
" . $dockerComposeCmd . " -f " . escapeshellarg($dockerComposeFile) . " up -d frontend
echo \"[\$(date)] Restarting services...\"
" . $dockerComposeCmd . " -f " . escapeshellarg($dockerComposeFile) . " restart api-gateway monitoring-service bot-manager
echo \"[\$(date)] Done.\"
";

        $scriptPath = storage_path('app/restart_services.sh');
        file_put_contents($scriptPath, $script);
        chmod($scriptPath, 0755);

        $logFile = storage_path('logs/restart.log');
        $command = "nohup " . escapeshellarg($scriptPath) . " > " . escapeshellarg($logFile) . " 2>&1 &";
        exec($command);
        
        \Log::info('Triggered background restart script: ' . $command);
    }

    protected function updateEnvFile(string $path, string $key, string $value): void
    {
        try {
            if (! file_exists($path)) {
                file_put_contents($path, '');
            }

            $contents = file_get_contents($path);
            $escapedKey = preg_quote($key, '/');
            $pattern = "/^{$escapedKey}\s*=\s*.*$/m";
            $replacement = "{$key}=\"{$value}\"";
            $newContents = preg_replace($pattern, $replacement, $contents, 1, $count);

            if ($count === 0) {
                $newContents = rtrim($contents) . "\n{$key}=\"{$value}\"\n";
            }

            file_put_contents($path, $newContents);
            \Log::info("Updated {$key} in {$path}");
        } catch (\Exception $e) {
            \Log::warning("Failed to update {$key} in {$path}: " . $e->getMessage());
        }
    }
}

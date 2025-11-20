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

            // Update domain, email, and internal API key
            $this->updateEnvFiles($validated['dashboard_domain'], $validated['email']);

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
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Setup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function updateEnvFiles(string $domain, string $email): void
    {
        $rootEnvPath = base_path('../../.env');
        $apiGatewayEnvPath = base_path('.env');
        $monitoringServiceEnvPath = base_path('../../backend/monitoring-service/.env');
        $botManagerEnvPath = base_path('../../backend/bot-manager/.env');

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

        // Trigger docker-compose restart
        try {
            exec('cd ' . base_path('../..') . ' && docker-compose restart api-gateway frontend monitoring-service bot-manager > /dev/null 2>&1 &');
        } catch (\Exception $e) {
            \Log::warning('Failed to restart containers: ' . $e->getMessage());
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



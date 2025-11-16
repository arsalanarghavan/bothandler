<?php

namespace App\Services;

use App\Models\Bot;
use App\Models\Deployment;
use Symfony\Component\Process\Process;

class BotDeploymentService
{
    public function deployAsync(Bot $bot): Deployment
    {
        $deployment = Deployment::create([
            'bot_id' => $bot->id,
            'status' => 'queued',
            'started_at' => now(),
        ]);

        // In a real environment this should dispatch a queued job.
        // For now we trigger the shell command synchronously to keep the implementation simple.
        $this->runDeployment($bot, $deployment);

        return $deployment->fresh();
    }

    protected function runDeployment(Bot $bot, Deployment $deployment): void
    {
        $botDir = '/var/bots/' . $bot->id;

        if (! is_dir($botDir)) {
            mkdir($botDir, 0755, true);
        }

        // Step 1: clone or pull latest code
        $commands = [
            sprintf(
                'cd %s && if [ ! -d ".git" ]; then git clone %s .; else git pull origin %s; fi',
                $botDir,
                escapeshellarg($bot->github_repo_url),
                escapeshellarg($bot->github_branch ?? 'main')
            ),
        ];

        // Step 2: deployment strategy
        if (! empty($bot->deploy_command)) {
            // Custom deploy command defined by user
            $commands[] = sprintf('cd %s && %s', $botDir, $bot->deploy_command);
        } elseif (file_exists($botDir . '/docker-compose.yml') || file_exists($botDir . '/docker-compose.yaml')) {
            // Prefer docker compose if present
            $commands[] = sprintf('cd %s && docker compose up -d --build', $botDir);
        } else {
            // Fallback: single-container image based on directory Dockerfile
            $serviceName = 'service-' . $bot->id;
            $commands[] = sprintf(
                'cd %s && docker build -t %s . && docker rm -f %s || true && docker run -d --name %s --label com.bothandler.bot_id=%d --label com.bothandler.service_type=%s %s %s',
                $botDir,
                escapeshellarg($serviceName),
                escapeshellarg($serviceName),
                escapeshellarg($serviceName),
                $bot->id,
                escapeshellarg($bot->service_type ?? 'generic'),
                $bot->domain ? '-e VIRTUAL_HOST=' . escapeshellarg($bot->domain) . ' -e LETSENCRYPT_HOST=' . escapeshellarg($bot->domain) : '',
                escapeshellarg($serviceName)
            );
        }

        $fullCommand = implode(' && ', $commands);

        $process = Process::fromShellCommandline($fullCommand);
        $process->setTimeout(600);
        $process->run();

        $bot->update([
            'status' => $process->isSuccessful() ? 'active' : 'error',
            'last_deployed_at' => now(),
        ]);

        $deployment->update([
            'status' => $process->isSuccessful() ? 'success' : 'failed',
            'log' => $process->getOutput() . PHP_EOL . $process->getErrorOutput(),
            'finished_at' => now(),
        ]);
    }
}



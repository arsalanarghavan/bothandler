<?php

namespace App\Services;

use GuzzleHttp\Client;

class BotManagerClient
{
    private Client $client;

    public function __construct()
    {
        $baseUri = config('services.bot_manager.base_uri', 'http://bot-manager');

        $this->client = new Client([
            'base_uri' => rtrim($baseUri, '/') . '/api/',
            'timeout' => 5.0,
        ]);
    }

    public function listBots(): array
    {
        $response = $this->client->get('bots');
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function createBot(array $payload): array
    {
        $response = $this->client->post('bots', ['json' => $payload]);
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function getBot(int $id): array
    {
        $response = $this->client->get("bots/{$id}");
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function deployBot(int $id): array
    {
        $response = $this->client->post("bots/{$id}/deploy");
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function deployments(int $id): array
    {
        $response = $this->client->get("bots/{$id}/deployments");
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function deployment(int $deploymentId): array
    {
        $response = $this->client->get("deployments/{$deploymentId}");
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function deleteBot(int $id): array
    {
        $response = $this->client->delete("bots/{$id}");
        return json_decode((string) $response->getBody(), true);
    }

    public function updateAll(): array
    {
        $response = $this->client->post('bots/update-all');
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function deleteAll(): array
    {
        $response = $this->client->delete('bots');
        return json_decode((string) $response->getBody(), true);
    }
}



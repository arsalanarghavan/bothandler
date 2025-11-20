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
            'headers' => [
                'X-Internal-API-Key' => env('INTERNAL_API_KEY', 'change-this-in-production'),
            ],
        ]);
    }

    public function listBots(): array
    {
        try {
            $response = $this->client->get('bots');
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Bot manager listBots request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error('Bot manager listBots error: ' . $e->getMessage());
            return [];
        }
    }

    public function createBot(array $payload): array
    {
        try {
            $response = $this->client->post('bots', ['json' => $payload]);
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Bot manager createBot request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \Exception('Failed to create bot: ' . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage()));
        } catch (\Exception $e) {
            \Log::error('Bot manager createBot error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBot(int $id): array
    {
        try {
            $response = $this->client->get("bots/{$id}");
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Bot manager getBot request failed for ID {$id}: " . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error("Bot manager getBot error for ID {$id}: " . $e->getMessage());
            return [];
        }
    }

    public function deployBot(int $id): array
    {
        try {
            $response = $this->client->post("bots/{$id}/deploy");
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Bot manager deployBot request failed for ID {$id}: " . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \Exception('Failed to deploy bot: ' . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage()));
        } catch (\Exception $e) {
            \Log::error("Bot manager deployBot error for ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function deployments(int $id): array
    {
        try {
            $response = $this->client->get("bots/{$id}/deployments");
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Bot manager deployments request failed for ID {$id}: " . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error("Bot manager deployments error for ID {$id}: " . $e->getMessage());
            return [];
        }
    }

    public function deployment(int $deploymentId): array
    {
        try {
            $response = $this->client->get("deployments/{$deploymentId}");
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Bot manager deployment request failed for ID {$deploymentId}: " . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error("Bot manager deployment error for ID {$deploymentId}: " . $e->getMessage());
            return [];
        }
    }

    public function deleteBot(int $id): array
    {
        try {
            $response = $this->client->delete("bots/{$id}");
            return json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("Bot manager deleteBot request failed for ID {$id}: " . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \Exception('Failed to delete bot: ' . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage()));
        } catch (\Exception $e) {
            \Log::error("Bot manager deleteBot error for ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateAll(): array
    {
        try {
            $response = $this->client->post('bots/update-all');
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Bot manager updateAll request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \Exception('Failed to update all bots: ' . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage()));
        } catch (\Exception $e) {
            \Log::error('Bot manager updateAll error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteAll(): array
    {
        try {
            $response = $this->client->delete('bots');
            return json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Bot manager deleteAll request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \Exception('Failed to delete all bots: ' . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage()));
        } catch (\Exception $e) {
            \Log::error('Bot manager deleteAll error: ' . $e->getMessage());
            throw $e;
        }
    }
}



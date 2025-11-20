<?php

namespace App\Services;

use GuzzleHttp\Client;

class MonitoringClient
{
    private Client $client;

    public function __construct()
    {
        $baseUri = config('services.monitoring.base_uri', 'http://monitoring-service');

        $this->client = new Client([
            'base_uri' => rtrim($baseUri, '/') . '/api/',
            'timeout' => 3.0,
            'headers' => [
                'X-Internal-API-Key' => env('INTERNAL_API_KEY', 'change-this-in-production'),
            ],
        ]);
    }

    public function summary(): array
    {
        try {
            $response = $this->client->get('summary');
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Monitoring service summary request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error('Monitoring service summary error: ' . $e->getMessage());
            return [];
        }
    }

    public function containers(): array
    {
        try {
            $response = $this->client->get('containers');
            return json_decode((string) $response->getBody(), true)['data'] ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Monitoring service containers request failed: ' . $e->getMessage(), [
                'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            return [];
        } catch (\Exception $e) {
            \Log::error('Monitoring service containers error: ' . $e->getMessage());
            return [];
        }
    }
}



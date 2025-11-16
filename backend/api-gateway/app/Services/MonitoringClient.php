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
        ]);
    }

    public function summary(): array
    {
        $response = $this->client->get('summary');
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }

    public function containers(): array
    {
        $response = $this->client->get('containers');
        return json_decode((string) $response->getBody(), true)['data'] ?? [];
    }
}



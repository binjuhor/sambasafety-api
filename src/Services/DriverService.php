<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Models\Driver;

class DriverService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function list(array $filters = []): array
    {
        $response = $this->client->get('/drivers', $filters);

        return array_map(fn($data) => new Driver($data), $response['data'] ?? []);
    }

    public function get(string $driverId): Driver
    {
        $response = $this->client->get("/drivers/{$driverId}");

        return new Driver($response['data'] ?? $response);
    }

    public function create(array $driverData): Driver
    {
        $response = $this->client->post('/drivers', $driverData);

        return new Driver($response['data'] ?? $response);
    }

    public function getMvr(string $driverId): array
    {
        return $this->client->get("/drivers/{$driverId}/mvr");
    }
}
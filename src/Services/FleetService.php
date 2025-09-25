<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Models\Fleet;
use Binjuhor\SambasafetyApi\Collections\DriverCollection;

class FleetService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function list(array $filters = []): array
    {
        $response = $this->client->get('/fleets', $filters);

        return array_map(
            fn($data) => new Fleet($data),
            $response['data'] ?? []
        );
    }

    public function get(string $fleetId): Fleet
    {
        $response = $this->client->get("/fleets/{$fleetId}");

        return new Fleet($response['data'] ?? $response);
    }

    public function create(array $fleetData): Fleet
    {
        $response = $this->client->post('/fleets', $fleetData);

        return new Fleet($response['data'] ?? $response);
    }

    public function update(string $fleetId, array $fleetData): Fleet
    {
        $response = $this->client->put("/fleets/{$fleetId}", $fleetData);

        return new Fleet($response['data'] ?? $response);
    }

    public function delete(string $fleetId): bool
    {
        $this->client->delete("/fleets/{$fleetId}");
        return true;
    }

    public function getDrivers(string $fleetId, array $filters = []): DriverCollection
    {
        $response = $this->client->get("/fleets/{$fleetId}/drivers", $filters);

        $drivers = array_map(
            fn($data) => new \Binjuhor\SambasafetyApi\Models\Driver($data),
            $response['data'] ?? []
        );

        return new DriverCollection($drivers, $response['meta'] ?? null);
    }

    public function addDriver(string $fleetId, string $driverId): bool
    {
        $this->client->post("/fleets/{$fleetId}/drivers", ['driver_id' => $driverId]);
        return true;
    }

    public function removeDriver(string $fleetId, string $driverId): bool
    {
        $this->client->delete("/fleets/{$fleetId}/drivers/{$driverId}");
        return true;
    }
}
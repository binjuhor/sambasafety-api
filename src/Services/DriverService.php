<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Models\Driver;
use Binjuhor\SambasafetyApi\Models\MvrRecord;
use Binjuhor\SambasafetyApi\Collections\DriverCollection;
use Binjuhor\SambasafetyApi\Collections\MvrCollection;
use Binjuhor\SambasafetyApi\Query\DriverQuery;
use Binjuhor\SambasafetyApi\Validation\DriverValidator;

class DriverService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function query(): DriverQuery
    {
        return new DriverQuery($this->client);
    }

    public function list(array $filters = []): DriverCollection
    {
        $response = $this->client->get('/drivers', $filters);

        $drivers = array_map(
            fn($data) => new Driver($data),
            $response['data'] ?? []
        );

        return new DriverCollection($drivers, $response['meta'] ?? null);
    }

    public function get(string $driverId): Driver
    {
        $response = $this->client->get("/drivers/{$driverId}");

        return new Driver($response['data'] ?? $response);
    }

    public function create(array $driverData): Driver
    {
        DriverValidator::validateCreateData($driverData);

        $response = $this->client->post('/drivers', $driverData);

        return new Driver($response['data'] ?? $response);
    }

    public function update(string $driverId, array $driverData): Driver
    {
        DriverValidator::validateUpdateData($driverData);

        $response = $this->client->put("/drivers/{$driverId}", $driverData);

        return new Driver($response['data'] ?? $response);
    }

    public function delete(string $driverId): bool
    {
        $this->client->delete("/drivers/{$driverId}");
        return true;
    }

    public function getMvr(string $driverId): MvrRecord
    {
        $response = $this->client->get("/drivers/{$driverId}/mvr");

        return new MvrRecord($response['data'] ?? $response);
    }

    public function requestMvr(string $driverId, array $options = []): MvrRecord
    {
        $response = $this->client->post("/drivers/{$driverId}/mvr", $options);

        return new MvrRecord($response['data'] ?? $response);
    }

    public function getMvrHistory(string $driverId, array $filters = []): MvrCollection
    {
        $response = $this->client->get("/drivers/{$driverId}/mvr/history", $filters);

        $records = array_map(
            fn($data) => new MvrRecord($data),
            $response['data'] ?? []
        );

        return new MvrCollection($records, $response['meta'] ?? null);
    }

    public function activate(string $driverId): Driver
    {
        return $this->updateStatus($driverId, 'active');
    }

    public function deactivate(string $driverId): Driver
    {
        return $this->updateStatus($driverId, 'inactive');
    }

    public function suspend(string $driverId, string $reason = ''): Driver
    {
        return $this->updateStatus($driverId, 'suspended', ['reason' => $reason]);
    }

    private function updateStatus(string $driverId, string $status, array $extra = []): Driver
    {
        $data = array_merge(['status' => $status], $extra);
        $response = $this->client->patch("/drivers/{$driverId}/status", $data);

        return new Driver($response['data'] ?? $response);
    }
}
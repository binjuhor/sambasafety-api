<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Models\MvrRecord;
use Binjuhor\SambasafetyApi\Collections\MvrCollection;

class MvrService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function list(array $filters = []): MvrCollection
    {
        $response = $this->client->get('/mvr-records', $filters);

        $records = array_map(
            fn($data) => new MvrRecord($data),
            $response['data'] ?? []
        );

        return new MvrCollection($records, $response['meta'] ?? null);
    }

    public function get(string $recordId): MvrRecord
    {
        $response = $this->client->get("/mvr-records/{$recordId}");

        return new MvrRecord($response['data'] ?? $response);
    }

    public function request(array $requestData): MvrRecord
    {
        $response = $this->client->post('/mvr-records', $requestData);

        return new MvrRecord($response['data'] ?? $response);
    }

    public function requestForDriver(string $driverId, array $options = []): MvrRecord
    {
        $requestData = array_merge(['driver_id' => $driverId], $options);
        return $this->request($requestData);
    }

    public function getByDriver(string $driverId, array $filters = []): MvrCollection
    {
        $filters['driver_id'] = $driverId;
        return $this->list($filters);
    }

    public function getLatestByDriver(string $driverId): ?MvrRecord
    {
        $collection = $this->getByDriver($driverId, ['limit' => 1, 'sort' => '-created_at']);
        return $collection->first();
    }

    public function getPendingRecords(): MvrCollection
    {
        return $this->list(['status' => 'pending']);
    }

    public function getCompletedRecords(): MvrCollection
    {
        return $this->list(['status' => 'completed']);
    }

    public function cancel(string $recordId): bool
    {
        $this->client->patch("/mvr-records/{$recordId}", ['status' => 'cancelled']);
        return true;
    }

    public function refresh(string $recordId): MvrRecord
    {
        $response = $this->client->post("/mvr-records/{$recordId}/refresh");

        return new MvrRecord($response['data'] ?? $response);
    }
}
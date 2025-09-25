<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi;

use Binjuhor\SambasafetyApi\Services\DriverService;
use Binjuhor\SambasafetyApi\Services\FleetService;
use Binjuhor\SambasafetyApi\Services\MvrService;

class SambaSafety
{
    private SambaSafetyClient $client;
    private ?DriverService $drivers = null;
    private ?FleetService $fleets = null;
    private ?MvrService $mvr = null;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.sambasafety.com/v1',
        array $options = []
    ) {
        $this->client = new SambaSafetyClient($apiKey, $baseUrl, $options);
    }

    public function drivers(): DriverService
    {
        if ($this->drivers === null) {
            $this->drivers = new DriverService($this->client);
        }

        return $this->drivers;
    }

    public function fleets(): FleetService
    {
        if ($this->fleets === null) {
            $this->fleets = new FleetService($this->client);
        }

        return $this->fleets;
    }

    public function mvr(): MvrService
    {
        if ($this->mvr === null) {
            $this->mvr = new MvrService($this->client);
        }

        return $this->mvr;
    }

    public function getClient(): SambaSafetyClient
    {
        return $this->client;
    }

    public static function create(string $apiKey, string $baseUrl = 'https://api.sambasafety.com/v1'): self
    {
        return new self($apiKey, $baseUrl);
    }
}
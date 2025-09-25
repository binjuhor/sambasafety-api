<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi;

use Binjuhor\SambasafetyApi\Services\DriverService;

class SambaSafety
{
    private SambaSafetyClient $client;
    private ?DriverService $drivers = null;

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

    public static function create(string $apiKey, string $baseUrl = 'https://api.sambasafety.com/v1'): self
    {
        return new self($apiKey, $baseUrl);
    }
}
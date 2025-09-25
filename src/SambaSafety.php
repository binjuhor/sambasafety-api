<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi;

use Binjuhor\SambasafetyApi\Services\DriverService;
use Binjuhor\SambasafetyApi\Services\FleetService;
use Binjuhor\SambasafetyApi\Services\MvrService;
use Binjuhor\SambasafetyApi\Services\AuthService;
use Binjuhor\SambasafetyApi\Services\LicenseDiscoveryService;

class SambaSafety
{
    private SambaSafetyClient $client;
    private ?DriverService $drivers = null;
    private ?FleetService $fleets = null;
    private ?MvrService $mvr = null;
    private ?AuthService $auth = null;
    private ?LicenseDiscoveryService $licenseDiscovery = null;

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

    public function auth(): AuthService
    {
        if ($this->auth === null) {
            $this->auth = new AuthService($this->client);
        }

        return $this->auth;
    }

    public function licenseDiscovery(): LicenseDiscoveryService
    {
        if ($this->licenseDiscovery === null) {
            $this->licenseDiscovery = new LicenseDiscoveryService($this->client);
        }

        return $this->licenseDiscovery;
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
<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Models\LicenseInfo;
use Binjuhor\SambasafetyApi\Models\Driver;

class LicenseDiscoveryService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function discoverByPersonalInfo(array $personalInfo): array
    {
        $response = $this->client->post('/license-discovery/personal', $personalInfo);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function discoverByDriverInfo(string $firstName, string $lastName, string $dateOfBirth, ?string $state = null): array
    {
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $dateOfBirth,
        ];

        if ($state !== null) {
            $data['state'] = $state;
        }

        return $this->discoverByPersonalInfo($data);
    }

    public function discoverBySsn(string $ssn, ?string $state = null): array
    {
        $data = ['ssn' => $ssn];

        if ($state !== null) {
            $data['state'] = $state;
        }

        $response = $this->client->post('/license-discovery/ssn', $data);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function discoverMultipleStates(array $personalInfo, array $states): array
    {
        $data = array_merge($personalInfo, ['states' => $states]);
        $response = $this->client->post('/license-discovery/multi-state', $data);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function searchAllStates(array $personalInfo): array
    {
        $response = $this->client->post('/license-discovery/all-states', $personalInfo);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function validateLicense(string $licenseNumber, string $state): ?LicenseInfo
    {
        $response = $this->client->post('/license-discovery/validate', [
            'license_number' => $licenseNumber,
            'state' => $state,
        ]);

        if (empty($response['license'])) {
            return null;
        }

        return new LicenseInfo($response['license']);
    }

    public function getLicenseHistory(string $licenseNumber, string $state): array
    {
        $response = $this->client->get("/license-discovery/history/{$state}/{$licenseNumber}");

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['history'] ?? []
        );
    }

    public function findExpiredLicenses(array $filters = []): array
    {
        $response = $this->client->get('/license-discovery/expired', $filters);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function findExpiringSoon(int $days = 30): array
    {
        $response = $this->client->get('/license-discovery/expiring-soon', [
            'days' => $days
        ]);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function findSuspendedLicenses(array $filters = []): array
    {
        $response = $this->client->get('/license-discovery/suspended', $filters);

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function bulkValidation(array $licenses): array
    {
        $response = $this->client->post('/license-discovery/bulk-validate', [
            'licenses' => $licenses
        ]);

        $results = [];
        foreach ($response['results'] ?? [] as $result) {
            $results[] = [
                'license_number' => $result['license_number'],
                'state' => $result['state'],
                'valid' => $result['valid'],
                'license_info' => isset($result['license_info']) ? new LicenseInfo($result['license_info']) : null,
                'errors' => $result['errors'] ?? []
            ];
        }

        return $results;
    }

    public function discoverForExistingDriver(string $driverId): array
    {
        $response = $this->client->post("/drivers/{$driverId}/license-discovery");

        return array_map(
            fn($data) => new LicenseInfo($data),
            $response['licenses'] ?? []
        );
    }

    public function linkLicenseToDriver(string $driverId, string $licenseNumber, string $state): Driver
    {
        $response = $this->client->post("/drivers/{$driverId}/licenses", [
            'license_number' => $licenseNumber,
            'state' => $state
        ]);

        return new Driver($response['driver'] ?? $response);
    }

    public function unlinkLicenseFromDriver(string $driverId, string $licenseNumber, string $state): bool
    {
        $this->client->delete("/drivers/{$driverId}/licenses/{$state}/{$licenseNumber}");
        return true;
    }
}
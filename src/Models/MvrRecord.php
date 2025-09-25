<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class MvrRecord
{
    public string $id;
    public string $driverId;
    public string $state;
    public string $licenseNumber;
    public string $status;
    public ?DateTime $requestDate;
    public ?DateTime $reportDate;
    public array $violations = [];
    public array $accidents = [];
    public ?LicenseInfo $licenseInfo;
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->driverId = $data['driver_id'] ?? $data['driverId'] ?? '';
        $this->state = $data['state'] ?? '';
        $this->licenseNumber = $data['license_number'] ?? $data['licenseNumber'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->requestDate = $this->parseDate($data['request_date'] ?? $data['requestDate'] ?? null);
        $this->reportDate = $this->parseDate($data['report_date'] ?? $data['reportDate'] ?? null);

        $this->violations = array_map(
            fn($violation) => new Violation($violation),
            $data['violations'] ?? []
        );

        $this->accidents = array_map(
            fn($accident) => new Accident($accident),
            $data['accidents'] ?? []
        );

        $this->licenseInfo = isset($data['license_info']) || isset($data['licenseInfo'])
            ? new LicenseInfo($data['license_info'] ?? $data['licenseInfo'])
            : null;

        $this->metadata = $data['metadata'] ?? [];
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    public function hasAccidents(): bool
    {
        return !empty($this->accidents);
    }

    public function getViolationCount(): int
    {
        return count($this->violations);
    }

    public function getAccidentCount(): int
    {
        return count($this->accidents);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'driver_id' => $this->driverId,
            'state' => $this->state,
            'license_number' => $this->licenseNumber,
            'status' => $this->status,
            'request_date' => $this->requestDate?->format(DateTimeInterface::ATOM),
            'report_date' => $this->reportDate?->format(DateTimeInterface::ATOM),
            'violations' => array_map(fn($v) => $v->toArray(), $this->violations),
            'accidents' => array_map(fn($a) => $a->toArray(), $this->accidents),
            'license_info' => $this->licenseInfo?->toArray(),
            'metadata' => $this->metadata,
        ];
    }

    private function parseDate(?string $date): ?DateTime
    {
        if ($date === null) {
            return null;
        }

        return DateTime::createFromFormat(DateTimeInterface::ATOM, $date) ?: null;
    }
}
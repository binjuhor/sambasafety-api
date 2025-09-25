<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class Driver
{
    public string $id;
    public string $firstName;
    public string $lastName;
    public ?string $licenseNumber;
    public ?string $email;
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->firstName = $data['first_name'] ?? $data['firstName'] ?? '';
        $this->lastName = $data['last_name'] ?? $data['lastName'] ?? '';
        $this->licenseNumber = $data['license_number'] ?? $data['licenseNumber'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->metadata = $data['metadata'] ?? [];
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'license_number' => $this->licenseNumber,
            'email' => $this->email,
            'metadata' => $this->metadata,
        ];
    }
}
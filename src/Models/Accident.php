<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class Accident
{
    public string $id;
    public ?DateTime $date;
    public string $type;
    public string $severity;
    public ?string $location;
    public ?int $fatalities;
    public ?int $injuries;
    public ?float $damageAmount;
    public bool $atFault;
    public ?string $description;
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->date = $this->parseDate($data['date'] ?? null);
        $this->type = $data['type'] ?? 'unknown';
        $this->severity = $data['severity'] ?? 'minor';
        $this->location = $data['location'] ?? null;
        $this->fatalities = isset($data['fatalities']) ? (int) $data['fatalities'] : null;
        $this->injuries = isset($data['injuries']) ? (int) $data['injuries'] : null;
        $this->damageAmount = isset($data['damage_amount']) ? (float) $data['damage_amount'] : null;
        $this->atFault = (bool) ($data['at_fault'] ?? false);
        $this->description = $data['description'] ?? null;
        $this->metadata = $data['metadata'] ?? [];
    }

    public function isFatal(): bool
    {
        return $this->fatalities > 0;
    }

    public function hasInjuries(): bool
    {
        return $this->injuries > 0;
    }

    public function isPreventable(): bool
    {
        return $this->metadata['preventable'] ?? false;
    }

    public function isMajor(): bool
    {
        return in_array(strtolower($this->severity), ['major', 'serious', 'severe']) ||
               $this->isFatal() ||
               $this->hasInjuries();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->format(DateTimeInterface::ATOM),
            'type' => $this->type,
            'severity' => $this->severity,
            'location' => $this->location,
            'fatalities' => $this->fatalities,
            'injuries' => $this->injuries,
            'damage_amount' => $this->damageAmount,
            'at_fault' => $this->atFault,
            'description' => $this->description,
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
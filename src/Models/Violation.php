<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class Violation
{
    public string $id;
    public string $code;
    public string $description;
    public string $severity;
    public ?DateTime $date;
    public ?string $location;
    public ?float $fineAmount;
    public bool $conviction;
    public ?int $points;
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->code = $data['code'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->severity = $data['severity'] ?? 'minor';
        $this->date = $this->parseDate($data['date'] ?? null);
        $this->location = $data['location'] ?? null;
        $this->fineAmount = isset($data['fine_amount']) ? (float) $data['fine_amount'] : null;
        $this->conviction = (bool) ($data['conviction'] ?? false);
        $this->points = isset($data['points']) ? (int) $data['points'] : null;
        $this->metadata = $data['metadata'] ?? [];
    }

    public function isMajor(): bool
    {
        return in_array(strtolower($this->severity), ['major', 'serious', 'severe']);
    }

    public function isDui(): bool
    {
        return str_contains(strtolower($this->description), 'dui') ||
               str_contains(strtolower($this->description), 'dwi') ||
               str_contains(strtolower($this->code), 'dui');
    }

    public function isMovingViolation(): bool
    {
        $movingCodes = ['speeding', 'reckless', 'following', 'lane'];
        $desc = strtolower($this->description);

        foreach ($movingCodes as $code) {
            if (str_contains($desc, $code)) {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'severity' => $this->severity,
            'date' => $this->date?->format(DateTimeInterface::ATOM),
            'location' => $this->location,
            'fine_amount' => $this->fineAmount,
            'conviction' => $this->conviction,
            'points' => $this->points,
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
<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class Fleet
{
    public string $id;
    public string $name;
    public ?string $description;
    public string $status;
    public array $settings;
    public ?DateTime $createdAt;
    public ?DateTime $updatedAt;
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? 'active';
        $this->settings = $data['settings'] ?? [];
        $this->createdAt = $this->parseDate($data['created_at'] ?? $data['createdAt'] ?? null);
        $this->updatedAt = $this->parseDate($data['updated_at'] ?? $data['updatedAt'] ?? null);
        $this->metadata = $data['metadata'] ?? [];
    }

    public function isActive(): bool
    {
        return strtolower($this->status) === 'active';
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'settings' => $this->settings,
            'created_at' => $this->createdAt?->format(DateTimeInterface::ATOM),
            'updated_at' => $this->updatedAt?->format(DateTimeInterface::ATOM),
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
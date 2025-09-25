<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Models;

use DateTime;
use DateTimeInterface;

class LicenseInfo
{
    public string $number;
    public string $state;
    public string $status;
    public string $class;
    public ?DateTime $issueDate;
    public ?DateTime $expirationDate;
    public array $endorsements = [];
    public array $restrictions = [];
    public array $metadata;

    public function __construct(array $data = [])
    {
        $this->number = $data['number'] ?? '';
        $this->state = $data['state'] ?? '';
        $this->status = $data['status'] ?? 'active';
        $this->class = $data['class'] ?? 'regular';
        $this->issueDate = $this->parseDate($data['issue_date'] ?? $data['issueDate'] ?? null);
        $this->expirationDate = $this->parseDate($data['expiration_date'] ?? $data['expirationDate'] ?? null);
        $this->endorsements = $data['endorsements'] ?? [];
        $this->restrictions = $data['restrictions'] ?? [];
        $this->metadata = $data['metadata'] ?? [];
    }

    public function isActive(): bool
    {
        return strtolower($this->status) === 'active';
    }

    public function isSuspended(): bool
    {
        return in_array(strtolower($this->status), ['suspended', 'revoked', 'cancelled']);
    }

    public function isExpired(): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }

        return $this->expirationDate < new DateTime();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }

        $warningDate = (new DateTime())->modify("+{$days} days");
        return $this->expirationDate <= $warningDate;
    }

    public function hasEndorsement(string $endorsement): bool
    {
        return in_array(strtoupper($endorsement), array_map('strtoupper', $this->endorsements));
    }

    public function hasRestriction(string $restriction): bool
    {
        return in_array(strtoupper($restriction), array_map('strtoupper', $this->restrictions));
    }

    public function isCommercial(): bool
    {
        return str_starts_with(strtoupper($this->class), 'CDL') ||
               in_array(strtoupper($this->class), ['A', 'B', 'C']);
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'state' => $this->state,
            'status' => $this->status,
            'class' => $this->class,
            'issue_date' => $this->issueDate?->format(DateTimeInterface::ATOM),
            'expiration_date' => $this->expirationDate?->format(DateTimeInterface::ATOM),
            'endorsements' => $this->endorsements,
            'restrictions' => $this->restrictions,
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
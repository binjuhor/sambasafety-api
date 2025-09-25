<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Query;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Collections\DriverCollection;
use Binjuhor\SambasafetyApi\Models\Driver;

class DriverQuery
{
    private SambaSafetyClient $client;
    private array $filters = [];
    private array $sorts = [];
    private ?int $page = null;
    private ?int $perPage = null;
    private array $includes = [];

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function where(string $field, $value): self
    {
        $this->filters[$field] = $value;
        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $this->filters[$field] = implode(',', $values);
        return $this;
    }

    public function whereLike(string $field, string $value): self
    {
        $this->filters[$field . '_like'] = $value;
        return $this;
    }

    public function whereStatus(string $status): self
    {
        return $this->where('status', $status);
    }

    public function whereActive(): self
    {
        return $this->whereStatus('active');
    }

    public function whereInactive(): self
    {
        return $this->whereStatus('inactive');
    }

    public function whereState(string $state): self
    {
        return $this->where('state', $state);
    }

    public function whereEmail(string $email): self
    {
        return $this->where('email', $email);
    }

    public function whereLicenseNumber(string $licenseNumber): self
    {
        return $this->where('license_number', $licenseNumber);
    }

    public function whereCreatedAfter(string $date): self
    {
        return $this->where('created_after', $date);
    }

    public function whereCreatedBefore(string $date): self
    {
        return $this->where('created_before', $date);
    }

    public function sortBy(string $field, string $direction = 'asc'): self
    {
        $this->sorts[$field] = $direction;
        return $this;
    }

    public function sortByName(string $direction = 'asc'): self
    {
        return $this->sortBy('name', $direction);
    }

    public function sortByCreatedAt(string $direction = 'desc'): self
    {
        return $this->sortBy('created_at', $direction);
    }

    public function page(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function include(string ...$relations): self
    {
        $this->includes = array_merge($this->includes, $relations);
        return $this;
    }

    public function includeMvr(): self
    {
        return $this->include('mvr');
    }

    public function includeFleet(): self
    {
        return $this->include('fleet');
    }

    public function get(): DriverCollection
    {
        $params = $this->buildQueryParams();
        $response = $this->client->get('/drivers', $params);

        $drivers = array_map(
            fn($data) => new Driver($data),
            $response['data'] ?? []
        );

        return new DriverCollection($drivers, $response['meta'] ?? null);
    }

    public function first(): ?Driver
    {
        $collection = $this->perPage(1)->get();
        return $collection->first();
    }

    public function count(): int
    {
        $params = $this->buildQueryParams();
        $params['count_only'] = true;

        $response = $this->client->get('/drivers', $params);

        return $response['count'] ?? 0;
    }

    private function buildQueryParams(): array
    {
        $params = $this->filters;

        if (!empty($this->sorts)) {
            $sortStrings = [];
            foreach ($this->sorts as $field => $direction) {
                $sortStrings[] = $direction === 'desc' ? "-{$field}" : $field;
            }
            $params['sort'] = implode(',', $sortStrings);
        }

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->perPage !== null) {
            $params['per_page'] = $this->perPage;
        }

        if (!empty($this->includes)) {
            $params['include'] = implode(',', $this->includes);
        }

        return $params;
    }
}
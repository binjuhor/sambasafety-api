<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Collections;

use Binjuhor\SambasafetyApi\Models\Driver;

class DriverCollection extends Collection
{
    public function findByEmail(string $email): ?Driver
    {
        return $this->find(fn(Driver $driver) => $driver->email === $email);
    }

    public function findByLicenseNumber(string $licenseNumber): ?Driver
    {
        return $this->find(fn(Driver $driver) => $driver->licenseNumber === $licenseNumber);
    }

    public function filterByStatus(string $status): DriverCollection
    {
        return $this->filter(fn(Driver $driver) => $driver->metadata['status'] ?? null === $status);
    }

    public function getActiveDrivers(): DriverCollection
    {
        return $this->filterByStatus('active');
    }

    public function getInactiveDrivers(): DriverCollection
    {
        return $this->filterByStatus('inactive');
    }

    public function sortByName(): DriverCollection
    {
        $sorted = $this->items;
        usort($sorted, fn(Driver $a, Driver $b) => strcmp($a->getFullName(), $b->getFullName()));

        return new static($sorted, $this->meta);
    }
}
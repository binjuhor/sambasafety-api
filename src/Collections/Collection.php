<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

abstract class Collection implements Countable, IteratorAggregate
{
    protected array $items = [];
    protected ?array $meta = null;

    public function __construct(array $items = [], ?array $meta = null)
    {
        $this->items = $items;
        $this->meta = $meta;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function toArray(): array
    {
        return array_map(
            fn($item) => method_exists($item, 'toArray') ? $item->toArray() : $item,
            $this->items
        );
    }

    public function first()
    {
        return $this->items[0] ?? null;
    }

    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback), $this->meta);
    }

    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items), $this->meta);
    }

    public function find(callable $callback)
    {
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    public function pluck(string $property): array
    {
        return array_map(
            fn($item) => is_object($item) ? $item->$property : $item[$property] ?? null,
            $this->items
        );
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function getTotal(): ?int
    {
        return $this->meta['total'] ?? null;
    }

    public function getCurrentPage(): ?int
    {
        return $this->meta['current_page'] ?? null;
    }

    public function getPerPage(): ?int
    {
        return $this->meta['per_page'] ?? null;
    }

    public function hasNextPage(): bool
    {
        if ($this->meta === null) {
            return false;
        }

        $currentPage = $this->getCurrentPage();
        $total = $this->getTotal();
        $perPage = $this->getPerPage();

        if ($currentPage === null || $total === null || $perPage === null) {
            return false;
        }

        return $currentPage < ceil($total / $perPage);
    }

    public function hasPreviousPage(): bool
    {
        $currentPage = $this->getCurrentPage();
        return $currentPage !== null && $currentPage > 1;
    }
}
<?php

namespace App\Repository;

use Iterator;
use Countable;

class AbstractCollection implements Iterator, Countable
{
    private array $items;
    private int $changes;

    public function __construct()
    {
        $this->items = [];
        $this->resetChanges();
    }

    public function items(): array
    {
        return $this->items;
    }

    public function set(mixed $item, mixed $index): void
    {
        if ($index) {
            if (!isset($this->items[$index]) || $this->items[$index] !== $item) {
                $this->change();
            }
            $this->items[$index] = $item;
        } else {
            $this->items[] = $item;
            $this->change();
        }
    }

    public function get(mixed $index): mixed
    {
        if (isset($this->items[$index])) {
            return clone $this->items[$index];
        }
        return null;
    }

    public function delete(mixed $item): void
    {
        foreach ($this->items as $key => $i) {
            if ($i === $item) {
                unset($this->items[$key]);
                return;
            }
        }
    }

    public function clear(): void
    {
        if (!empty($this->items)) {
            $this->items = [];
            $this->change();
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function change(): void
    {
        $this->changes++;
    }

    public function hasChanged(): bool
    {
        return ($this->changes > 0) ? true : false;
    }

    public function resetChanges(): void
    {
        $this->changes = 0;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function current(): mixed
    {
        return current($this->items);
    }

    public function key(): int
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }
}

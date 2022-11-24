<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use Iterator;
use Countable;

/**
 * A traversable collection.
 *
 * Can contain duplicate items.
 * Cannot contain duplicate indexes (keys).
 * Tracks number of changes.
 */
class Collection implements Iterator, Countable
{
    private array $items;
    private int $changes;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->items = [];
        $this->resetChanges();
    }

    /**
     * Access to all item objects.
     *
     * @return array Associative rray of items.
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Stores an item in the collection.
     *
     * @param mixed $item  An item to be stored.
     * @param mixed $index If provided, the item would be stored under that value as a key in associative array.
     */
    public function set(mixed $item, mixed $index = null): void
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

    /**
     * Gets an item from the collection
     *
     * @param mixed $index A key in the associative array of items.
     *
     * @return mixed A cloned copy of item.
     */
    public function get(mixed $index): mixed
    {
        if (isset($this->items[$index])) {
            return clone $this->items[$index];
        }
        return null;
    }

    /**
     * Removes an item from the collection
     *
     * @oaran mixed $item A item or its clone. Aa items strictly equal to the item provided would be removed.
     */
    public function delete(mixed $item): void
    {
        foreach ($this->items as $key => $i) {
            if ($i === $item) {
                unset($this->items[$key]);
                return;
            }
        }
    }

    /**
     * Removes all items from the colletion.
     */
    public function clear(): void
    {
        if (!empty($this->items)) {
            $this->items = [];
            $this->change();
        }
    }

    /**
     * Checks if there are any items in the collection.
     *
     * @return bool True if there are no items; false otherwise.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * In order to track number of changes, this function should be called wherever a change is made.
     */
    public function change(): void
    {
        $this->changes++;
    }

    /**
     * Checks if the content changed.
     *
     * @return bool True if the collection content was changed since last use of resetChanges(). False otherwise.
     */
    public function hasChanged(): bool
    {
        return ($this->changes > 0) ? true : false;
    }

    /**
     * Starts tracking changes, resets change counter to 0.
     */
    public function resetChanges(): void
    {
        $this->changes = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return current($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): int
    {
        return key($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return key($this->items) !== null;
    }
}

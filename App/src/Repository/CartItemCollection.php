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

use App\Repository\Collection;

/**
 * Collection of CartItem items.
 */
class CartItemCollection extends Collection
{
    /**
     * Updates or inserts a CartItem object into collection.
     * If there is already a CartItem with the same product, only its quantity would be updated.
     *
     * The resulting quantity will equal the sum of both quantities (of existing item and provided one).
     * Negative quantity value is interpreted as removing pieces of product from the cart.
     *
     * @param CartItem $item A CartItem object to be upserted.
     */
    public function upsert(CartItem $item): void
    {
        $productId = $item->getProduct()->getId();

        $cartItem = $this->get($productId);
        if ($cartItem) {
            $item->update($cartItem->getQuantity());
        }
        $this->set($item, $productId);
    }

    /**
     * Calculates cart total.
     *
     * @return int A total cart value in internal integer format. Divide by 100 to get a floating point number.
     */
    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->items() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $total;
    }

    /**
     * Generates a part of an API action return value.
     *
     * @param string $url Base URL of the API (f.e. http://localhost:8080)
     *
     * @return array JSON-able array of CartItem objects.
     */
    public function getJSON(string $url): array
    {
        $items = [];
        foreach ($this->items() as $item) {
            $items[] = $item->getJSON($url);
        }
        return $items;
    }
}

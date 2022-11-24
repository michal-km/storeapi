<?php

namespace App\Repository;

use App\Repository\AbstractCollection;

class CartItemCollection extends AbstractCollection
{
    public function upsert(CartItem $item): void
    {
        $productId = $item->getProduct()->getId();

        $cartItem = $this->get($productId);
        if ($cartItem) {
            $item->update($cartItem->getQuantity());
        }
        $this->set($item, $productId);
    }

    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->items() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $total;
    }


    public function getJSON(string $url): array
    {
        $items = [];
        foreach ($this->items() as $item) {
            $items[] = $item->getJSON($url);
        }
        return $items;
    }
}

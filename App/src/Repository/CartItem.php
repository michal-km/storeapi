<?php

namespace App\Repository;

class CartItem
{
    private int $id;
    private int $productId;
    private int $quantity;

    public function __construct(int $id, int $productId, int $quantity)
    {
        $this->id = $id;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function add(int $quantity): void
    {
        $this->quantity += $quantity;
        if ($quantity < 0) {
            $quantity = 0;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getJSON(): array
    {
        return [

        ];
    }
}

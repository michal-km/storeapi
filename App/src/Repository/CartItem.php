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

use App\Entity\Product;

class CartItem
{
    private ?int $id;
    private Product $product;
    private int $quantity;

    public function __construct(Product $product, int $quantity, ?int $id = null)
    {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->product = $product;
    }

    public function update(int $quantity): void
    {
        $this->quantity += $quantity;
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getJSON(string $url): array
    {
        return [
            'id' => $this->getProduct()->getId(),
            'quantity' => $this->getQuantity(),
            'link' => $url . 'catalog/api/v1/products/' . $this->getProduct()->getId(),
        ];
    }
}

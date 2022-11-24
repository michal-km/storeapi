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

/**
 * A representation of a single position in the cart.
 *
 * Can be also used as a command to add or substract given number of product pieces from other existing cart iitem.
 * In such case, a quantity could be negative.
 */
class CartItem
{
    private Product $product;
    private int $quantity;

    /**
     * @param Product $product  Product entity.
     * @param int     $quantity Negative value can  be provided as an intention of removing pieces from the cart.
     */
    public function __construct(Product $product, int $quantity)
    {
        $this->quantity = $quantity;
        $this->product = $product;
    }

    /**
     * Sums given quantity with existing one.
     * Negative values are accepted as a means of reducting existing quantity.
     * The resulting quantity will always be no less than 0.
     *
     * @param int $quantity Pieces of product to be added or substracted to/from thhe cart item position.
     */
    public function update(int $quantity): void
    {
        $this->quantity += $quantity;
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
    }

    /**
     * A product from the cart item.
     *
     * @return Product Product entity.
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * Returns number of product pieces in the cart item.
     *
     * @return int An integer, can be negative if the cart item was created in order to reduce product count.
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Generates a part of an API action return value.
     *
     * @param string $url Base URL of the API (f.e. http://localhost:8080)
     *
     * @return array JSON-able array of a CartItem object.
     */
    public function getJSON(string $url): array
    {
        return [
            'id' => $this->getProduct()->getId(),
            'quantity' => $this->getQuantity(),
            'link' => $url . 'catalog/api/v1/products/' . $this->getProduct()->getId(),
        ];
    }
}

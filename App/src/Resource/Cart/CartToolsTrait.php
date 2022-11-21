<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource\Cart;

use App\Entity\CartItem;
use App\Entity\Product;

trait CartToolsTrait
{
    /**
     * Returns all cart items added to a cart with given ID.
     *
     * @param string $cartId Cart identifier.
     *
     * @return array CartItem entities.
     */
    private function getCartItems(string $cartId): array
    {
        $em = $this->getEntityManager();
        $cartRepository = $em->getRepository(CartItem::class);
        return $cartRepository->findBy(['CartId' => $cartId]);
    }

    /**
     * Returns all cart items added to a cart with given ID.
     *
     * @param string $cartId Cart identifier.
     *
     * @return array Array for JSON response.
     */
    private function getCartList(string $cartId): array
    {
        $server = 'http://localhost:8080/';
        $items = [];
        $cartItems = $this->getCartItems($cartId);
        foreach ($cartItems as $item) {
            $items[] = [
                'id' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
                'link' => $server . 'catalog/api/v1/products/' . $item->getProductId(),
            ];
        }
        return $items;
    }

    /**
     * Returns total sum for all products added to the cart.
     *
     * @param string $cartId Cart identifier.
     *
     * @return float Cart total.
     */
    private function getCartTotal(string $cartId): float
    {
        $total = 0;
        $em = $this->getEntityManager();
        $productRepository = $em->getRepository(Product::class);
        $cartItems = $this->getCartItems($cartId);
        foreach ($cartItems as $item) {
            $product = $productRepository->find($item->getProductId());
            if ($product) {
                $total += $product->getPrice() * $item->getQuantity();
            }
        }
        return $total / 100;
    }

    /**
     * Returns cart representation with product list and total sum.
     *
     * @param string $cartId Cart identifier.
     *
     * @return array Array for JSON response.
     */
    private function getCartJSON(string $cartId): array
    {
        $data = [
            'items' => $this->getCartList($cartId),
            'meta' => [
                'cart.id' => $cartId,
                'cart.total' => $this->getCartTotal($cartId),
            ],
        ];
        return $data;
    }

    /**
     * Checks if the product exists in the database
     *
     * @param int $productId Product identifier.
     *
     * @return bool True if product exists, false otherwise.
     */
    private function isValidProduct(int $productId): bool
    {
        $em = $this->getEntityManager();
        $productRepository = $em->getRepository(Product::class);
        $product = $productRepository->find($productId);
        return (null !== $product) ? true : false;
    }

    /**
     * Creates a globally unique identifier for a new cart.
     *
     * @return string GUID.
     */
    private function createGUID(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

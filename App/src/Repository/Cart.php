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

use Doctrine\ORM\EntityManager;
use App\Repository\CartItem;
use App\Entity\Product;
use App\Repository\StorableCartItemCollection;
use App\Repository\GUID;
use App\Validator\Validator;

class Cart
{
    private StorableCartItemCollection $cartItems;
    private EntityManager $entityManager;
    private ?string $cartId;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cartItems = new StorableCartItemCollection($entityManager);
        $this->cartId = GUID::create();
    }

    public function load(?string $cartId): void
    {
        $this->cartId = GUID::create($cartId);
        $this->cartItems->load($this->cartId);
    }

    public function save(): void
    {
        $this->cartItems->save($this->cartId);
    }

    public function truncate(): void
    {
        $this->cartItems->truncate($this->cartId);
    }

    public function items(): CartItemCollection
    {
        return $this->cartItems;
    }

    /**
     * Adds or updates products in the cart.
     *
     * @param array $items Array containing products to be added or removed.
     */
    public function processRequestData(array $items): void
    {
        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                throw new \Exception('Invalid data', 400);
            }

            $id = Validator::validateInteger('id', $item['id']);
            $quantity = Validator::validateInteger('quantity', $item['quantity']);

            $product = $this->getProduct($id);
            if (empty($product)) {
                throw new \Exception('Not changed', 304);
            }

            $cartItem = new CartItem($product, $quantity);
            $this->cartItems->upsert($cartItem);
        }
        $this->validate();
    }

    /**
     * Returns products for a given cart identifier.
     *
     * @param string $cartId Cart identifier.
     *
     * @return array Array containing products added to the cart.
     */
    public function getJSON(string $url): array
    {
        return [
            'items' => $this->cartItems->getJSON($url),
            'meta' => [
                'cart.id' => $this->cartId,
                'cart.total' => $this->cartItems->getTotal() / 100,
            ],
        ];
    }

    /**
     * Validation before storing in the database.
     * Checks if there are no more than 10 pieces of single products.
     * Checks if there are no more than 3 products in the cart.
     */
    private function validate(): void
    {
        foreach ($this->cartItems as $item) {
            if ($item->getQuantity() > 10) {
                throw new \Exception('Only 10 pieces of the same product is allowed', 304);
            }
            if ($item->getQuantity() <= 0) {
                $this->cartItems->delete($item);
            }
        }

        if (count($this->cartItems) > 3) {
            throw new \Exception('Only 3 different products are allowed', 304);
        }
    }

    private function getProduct(int $id): ?Product
    {
        $product = $this->entityManager
            ->getRepository(Product::class)
            ->find($id);
        return $product;
    }
}

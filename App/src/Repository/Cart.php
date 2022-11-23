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
use App\Entity\CartItem as CartItemEntity;
use App\Entity\Product as ProductEntity;
use App\Repository\CartItem;
use App\Validator\Validator;

class Cart
{
    private EntityManager $entityManager;
    private array $cartItems;
    private ?string $cartId;
    private int $changes;

    public function __construct(EntityManager $entityManager, ?string $cartId = null)
    {
        $this->cartItems = [];
        $this->entityManager = $entityManager;
        $this->cartId = $this->getCartId($cartId);
        if ($cartId) {
            $this->load();
        }
        $this->resetChanges();
    }

    /**
     * If a product has quantity of 0 or less, it is removed from the cart.
     */
    public function addToCart(Cartitem $item): void
    {
        $quantity = $item->getQuantity();
        $productId = $item->getProductId();
        $this->getProduct($productId);

        if (isset($this->cartItems[$productId])) {
            $oldQuantity = $this->cartItems[$productId]->getQuantity();
            if ($oldQuantity != $quantity) {
                $this->cartItems[$productId]->add($quantity);
                $this->changes++;
            }
        } else {
            $this->cartItems[$productId] = $item;
            $this->changes++;
        }

        if ($this->cartItems[$productId]->getQuantity() <= 0) {
            $this->removeCartItem($productId);
            $this->changes++;
        }
    }

    public function clear(): void
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\CartItem', 'c')
            ->where('c.CartId = :guid')
            ->setParameter('guid', $this->cartId);
        $query = $qb->getQuery();
        $query->execute();
        $this->entityManager->flush();
        $this->cartItems = [];
        $this->resetChanges();
    }

    public function isEmpty(): bool
    {
        return (count($this->cartItems) === 0) ? true : false;
    }

    public function hasChanged(): bool
    {
        return ($this->changes > 0) ? true : false;
    }

    /**
     * Adds or updated products in the cart.
     *
     * @param array $items Array containing products to be added or removed.
     */
    public function update(array $items): void
    {
        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                throw new \Exception('Invalid data', 400);
            }

            $id = Validator::validateInteger('id', $item['id']);

            $quantity = Validator::validateInteger('quantity', $item['quantity']);
            if (0 !== $quantity) {
                $this->addToCart(new CartItem($id, $quantity));
            }
        }
    }

    /**
     * Stores cart content in the database.
     *
     * @param string $cartId    An unique cart identifier.
     */
    public function save(): void
    {
        $this->validate();

        $em = $this->entityManager;
        $db = $em->getRepository(CartItemEntity::class);

        foreach ($this->cartItems as $item) {
            if ($item->getId()) {
                $cartItem = $db->find($item->getId());
            } else {
                $cartItem = new CartItemEntity($this->cartId, $item->getProductId(), $item->getQuantity());
            }
            $cartItem->setQuantity($item->getQuantity());
            $em->persist($cartItem);
        }
        $em->flush();
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
        $items = [];
        $total = 0;
        foreach ($this->cartItems as $item) {
            $product = $this->entityManager->getRepository(ProductEntity::class)->find($item->getProductId());
            if ($product) {
                $items[] = [
                    'id' => $item->getProductId(),
                    'quantity' => $item->getQuantity(),
                    'link' => $url . 'catalog/api/v1/products/' . $item->getProductId(),
                ];
                $total += $product->getPrice() * $item->getQuantity();
            }
        }
        return [
            'items' => $items,
            'meta' => [
                'cart.id' => $this->cartId,
                'cart.total' => $total / 100,
            ],
        ];
    }

    private function resetChanges(): void
    {
        $this->changes = 0;
    }

    private function load(): void
    {
        $this->cartItems = [];
        if (null === $this->cartId) {
            return;
        }
        $cartRepository = $this->entityManager->getRepository(CartItemEntity::class);
        $c = $cartRepository->findBy(['CartId' => $this->cartId]);
        foreach ($c as $item) {
            $this->cartItems[$item->getProductId()] = new CartItem(
                $item->getProductId(),
                $item->getQuantity(),
                $item->getId()
            );
        }
    }

    private function removeCartItem(?int $productId): void
    {
        if (null === $productId) {
            return;
        }

        if (!isset($this->cartItems[$productId])) {
            return;
        }

        $em = $this->entityManager;
        $db = $em->getRepository(CartItemEntity::class);

        $cartItemId = $this->cartItems[$productId]->getId();
        if ($cartItemId) {
            $cartItem = $db->find($cartItemId);
            if ($cartItem) {
                $em->remove($cartItem);
                $em->flush();
            }
        }

        unset($this->cartItems[$productId]);
    }

    /**
     * Validates cart ID.
     * If ID is invalid or empty, a new unique identifier is created.
     *
     * @param mixed $idParam Card ID to be validated.
     *
     * @return string Validated ID.
     */
    public function getCartId(mixed $idParam): string
    {
        try {
            $cartId = Validator::validateGUID('id', $idParam);
        } catch (\Exception $e) {
            $cartId = $this->createGUID();
        }

        return $cartId;
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

    /**
     * Validation before storing in the database.
     * Checks if there are no more than 10 pieces of single products.
     * Checks if there are no more than 3 products in the cart.
     */
    private function validate(): void
    {
        $productCount = 0;
        foreach ($this->cartItems as $item) {
            $quantity = $item->getQuantity();
            if ($quantity > 0) {
                $productCount++;
                if ($quantity > 10) {
                    throw new \Exception('Only 10 pieces of the same product is allowed', 304);
                }
            }
        }
        if ($productCount > 3) {
            throw new \Exception('Only 3 different products are allowed', 304);
        }
    }

    private function getProduct(int $productId): ProductEntity
    {
        $productRepository = $this->entityManager->getRepository(ProductEntity::class);
        $product = $productRepository->find($productId);
        if (empty($product)) {
            throw new \Exception("Product not found", 304);
        }
        return $product;
    }
}

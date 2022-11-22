<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use App\Entity\CartItem as CartItemEntity;
use App\Entity\Product as ProductEntity;
use App\Repository\CartItem;

class Cart
{
    private ContainerInterface $serviceContainer;
    private EntityManager $entityManager;
    private array $cartItems;
    private ?string $cartId;
    private int $changes;

    public function __construct(ContainerInterface $serviceContainer, ?string $cartId = null)
    {
        $this->serviceContainer = $serviceContainer;
        $this->entityManager = $serviceContainer->get(EntityManager::class);
        $this->cartItems = [];
        $this->cartId = $cartId;
        $this->loadCartContents();
        $this->resetChanges();
    }

    public function addToCart(Cartitem $item): void
    {
        $quantity = $item->getQuantity();
        $id = $item->getId();

        if (isset($this->cartItems[$id])) {
            $oldQuantity = $this->cartItems[$id]->getQuantity();
            if ($oldQuantity != $quantity) {
                $this->cartItems[$iid]->add($quantity);
                $this->changes++;
            }
        } else {
            $this->cartItems[$id] = $item;
            $this->changes++;
        }

        if ($this->cartItems[$id]->getQuantity() <= 0) {
            unset($this->carItems[$id]);
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

    private function loadCartContents(): void
    {
        if (null === $this->cartId) {
            return;
        }
        $this->cartItems = [];
        $cartRepository = $this->entityManager->getRepository(CartItemEntity::class);
        $c = $cartRepository->findBy(['CartId' => $this->cartId]);
        foreach ($c as $item) {
            $this->cartItems[$item->getProductId()] = new CartItem(
                $item->getId(),
                $item->getProductId(),
                $item->getQuantity()
            );
        }
    }
}

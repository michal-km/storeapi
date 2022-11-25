<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use App\Repository\CartItemCollection;
use App\Entity\CartItem as CartItemEntity;
use App\Entity\Product as ProductEntity;

class StorableCartItemCollection extends CartItemCollection
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    public function load(?string $cartId = null): void
    {
        $this->clear();
        if (null !== $cartId) {
            $c = $this->entityManager
                ->getRepository(CartItemEntity::class)
                ->findBy(['CartId' => $cartId]);
            foreach ($c as $item) {
                $product = $this->entityManager->getRepository(ProductEntity::class)->find($item->getProductId());
                $this->upsert(new CartItem(
                    $product,
                    $item->getQuantity()
                ));
            }
        }
        $this->resetChanges();
    }

    /**
     * Stores cart content in the database.
     */
    public function save(string $cartId): void
    {
        $this->truncate($cartId);

        foreach ($this->items() as $item) {
            $cartItem = new CartItemEntity(
                $cartId,
                $item->getProduct()->getId(),
                $item->getQuantity()
            );
            $this->entityManager->persist($cartItem);
        }
        $this->entityManager->flush();
    }

    public function truncate(string $cartId): void
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\CartItem', 'c')
            ->where('c.CartId = :guid')
            ->setParameter('guid', $cartId);
        $query = $qb->getQuery();
        $query->execute();
        $this->entityManager->flush();
    }
}

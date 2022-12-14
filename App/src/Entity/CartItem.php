<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Cart item entity
 *
 * @OA\Schema(
 *     description="Cart Item",
 *     title="Cart Item",
 *     required={"CartId", "ProductId", "Quantity"},
 *     @OA\Xml(
 *         name="CartItem"
 *     )
 * )
 */
#[Entity, Table(name: 'cart')]
final class CartItem
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @OA\Property(
     *     description="Cart ID (globally uniqie identifier)",
     *     example="b0145a23-14db-4219-b02a-53de833e470d"
     * )
     *
     * @var string
     */
    #[Column(type: 'string', nullable: false)]
    private ?string $CartId = null;

    /**
     * @OA\Property(
     *     description="Product identifier",
     *     example=39
     * )
     *
     * @var int
     */
    #[Column(type: 'integer', nullable: false)]
    private ?int $ProductId = null;

    /**
     * @OA\Property(
     *     description="Number of pieces.",
     *     example=5
     * )
     *
     * @var int
     */
    #[Column(type: 'integer', nullable: false)]
    private ?int $Quantity = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $cartId, int $productId, int $quantity)
    {
        $this->CartId = $cartId;
        $this->ProductId = $productId;
        $this->Quantity = $quantity;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getCartId(): ?string
    {
        return $this->CartId;
    }

    /**
     * {@inheritDoc}
     */
    public function setCartId(string $CartId): self
    {
        $this->CartId = $CartId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductId(): ?int
    {
        return $this->ProductId;
    }

    /**
     * {@inheritDoc}
     */
    public function setProductId(int $ProductId): self
    {
        $this->ProductId = $ProductId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    /**
     * {@inheritDoc}
     */
    public function setQuantity(int $Quantity): self
    {
        $this->Quantity = $Quantity;

        return $this;
    }
}

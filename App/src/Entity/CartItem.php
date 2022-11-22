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
 */
#[Entity, Table(name: 'cart')]
final class CartItem
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @OA\Property(
     *     name="CartId",
     *     description="Carti ID (globally uniqie identifier)",
     *     title="Cart ID",
     *     example="b0145a23-14db-4219-b02a-53de833e470d"
     * )
     *
     * @var string
     */
    #[Column(type: 'string', nullable: false)]
    private ?string $CartId = null;

    /**
     * @OA\Property(
     *     name="ProductId",
     *     description="Product identifier",
     *     title="Product ID",
     *     example=39
     * )
     *
     * @var int
     */
    #[Column(type: 'integer', nullable: false)]
    private ?int $ProductId = null;

    /**
     * @OA\Property(
     *     name="Quantity",
     *     description="Number of pieces.",
     *     title="Quantity",
     *     example=5
     * )
     *
     * @var int
     */
    #[Column(type: 'integer', nullable: false)]
    private ?int $Quantity = null;

    public function __construct(string $cartId, int $productId, int $quantity)
    {
        $this->CartId = $cartId;
        $this->ProductId = $productId;
        $this->Quantity = $quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartId(): ?string
    {
        return $this->CartId;
    }

    public function setCartId(string $CartId): self
    {
        $this->CartId = $CartId;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->ProductId;
    }

    public function setProductId(int $ProductId): self
    {
        $this->ProductId = $ProductId;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): self
    {
        $this->Quantity = $Quantity;

        return $this;
    }
}

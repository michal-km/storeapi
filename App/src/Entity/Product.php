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
 * Product entity
 *
 * @OA\Schema(
 *     description="Product",
 *     title="Product",
 *     required={"Title", "Price"},
 *     @OA\Xml(
 *         name="Product"
 *     )
 * )
 */
#[Entity, Table(name: 'product')]
final class Product
{
    /**
     * @OA\Parameter(
     *     description="The product identifier",
     *     example=39
     * )
     */
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @OA\Property(
     *     description="Product title",
     *     example="Baldur's Gate",
     *     minLength=1,
     *     maxLength=255
     * )
     *
     * @var string
     */
    #[Column(type: 'string', unique: true, nullable: false)]
    private ?string $Title = null;

    /**
     * @OA\Property(
     *     description="Product price (floating point)",
     *     example=3.99,
     *     minimum=0
     * )
     *
     * A floating point number given in the API call will be multiplied by 100 and stored as an integer.
     *
     * @var int
     */
    #[Column(type: 'integer', nullable: false)]
    private ?int $Price = null;

    public function __construct(string $title, int $price)
    {
        $this->Title = $title;
        $this->Price = $price;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->Price;
    }

    public function setPrice(int $Price): self
    {
        $this->Price = $Price;

        return $this;
    }
}

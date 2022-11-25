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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Resource\AbstractResourceHandler;

/**
 * GET /store/openapi
 */

/**
     * @OA\Schema(
     *     schema="cart_item",
     *     type="array",
     *     @OA\Items(
     *         @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *         @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *     )
     * )
     *
     * @OA\Schema(
     *     schema="cart_meta",
     *     type="array",
     *     @OA\Items(
     *         @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *         @OA\Property(property="cart.total", type="number", example="99.99"),
     *     )
     * )
     *
     * @OA\Schema(
     *     schema="cart_info",
     *     type="array",
     *     @OA\Items(
     *         @OA\Property(property="items", title="items", ref="#/components/schemas/cart_item"),
     *         @OA\Property(property="meta", title="meta", ref="#/components/schemas/cart_meta"),
     *     )
     * )
     *
     * @OA\RequestBody(
     *     request="request_cart_items",
     *     required=true,
     *     description="Array with one or more products to be inserted to the cart.",
     *     @OA\JsonContent(
     *        required={"items"},
     *        @OA\Property(property="items", ref="#/components/schemas/cart_item"),
     *     )
     * )
     */

final class OpenApi extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $swagger = \OpenApi\Generator::scan([__DIR__, __DIR__ . '/../../Entity']);
        return $swagger;
    }
}

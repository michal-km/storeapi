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
use App\Resource\Cart\PutCart;

/**
 * PUT /store/api/v1/carts
 */
final class PutNewCart extends PutCart
{
    /**
     * {@inheritDoc}
     *
     * @OA\Put(
     *     tags={"cart"},
     *     path="/store/api/v1/carts",
     *     operationId="putNewCart",
     *     summary = "Creates a new cart and inserts a product to it.",
     *
     *     @OA\RequestBody(ref="#/components/requestBodies/request_cart_items"),
     *
     *     @OA\Response(response="201", description="Cart was created successfully", @OA\JsonContent(ref="#/components/schemas/cart_info")),
     *     @OA\Response(response="400", description="Invalid input data"),
     *     @OA\Response(response="500", description="Server error")
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        return parent::processRequest($request);
    }
}

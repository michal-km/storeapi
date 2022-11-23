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
use App\Repository\Cart;

/**
 * PUT /store/api/v1/carts/{id}
 */
class PutCart extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Put(
     *     tags={"cart"},
     *     path="/store/api/v1/carts/{id}",
     *     operationId="putCart",
     *     summary = "Inserts (or removes) a product to/from a cart with given identifier.",
     *     description = "Positive quantity value adds given number of pieces to the cart identified by ID. Negative quantity value substract appropriate number of pieces from the cart. If the resulting number is less than or equal to 0, the product is removed from the cart.",
     *
     *     @OA\Parameter(name="id", in="path", required=false, description="The cart ID", example="b0145a23-14db-4219-b02a-53de833e470d", @OA\Schema(type="string")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array with one or more products to be inserted or removed to/from the cart.",
     *         @OA\JsonContent(
     *            required={"items"},
     *            @OA\Property(
     *                property="items",
     *                type="array",
     *                @OA\Items(
     *                    @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                    @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                ),
     *            ),
     *         ),
     *      ),
     *
     *     @OA\Response(
     *      response="200",
     *      description="Cart was updated successfully",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *
     *     @OA\Response(
     *      response="201",
     *      description="Cart was created successfully",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *
     *     @OA\Response(
     *      response="304",
     *      description="Not changed",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Invalid input data",
     *     ),
     *
     *    @OA\Response(
     *      response="500",
     *      description="Server error",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $created = false;

        $params = $request->getParsedBody();
        if (!isset($params['items'])) {
            throw new \Exception('Not changed', 304);
        }

        // load or create cart
        $id = $request->getAttribute('id');
        $cart = new Cart($this->getEntityManager(), $id);
        if ($cart->isEmpty()) {
            $created = true;
        }

        // update with provided data
        $cart->update($params['items']);

        if (!$cart->hasChanged()) {
            throw new \Exception('Not changed', 304);
        }

        // save cart
        $cart->save();

        $data = $cart->getJSON($this->getServer());
        $data['code'] = $created ? 201 : 200;
        return $data;
    }
}

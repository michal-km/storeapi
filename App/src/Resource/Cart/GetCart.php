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
use Psr\Container\ContainerInterface;
use App\Resource\AbstractResourceHandler;
use App\Resource\Cart\CartToolsTrait;
use App\Validator\Validator;
use App\Repository\Cart;

/**
 * @OA\Server(url="http://localhost:8080")
 * @OA\Info(title="Cart API", version="1.0")
 *
 * GET /store/api/v1/carts/{id}
 */
final class GetCart extends AbstractResourceHandler implements RequestHandlerInterface
{
    use CartToolsTrait;

    /**
     * {@inheritDoc}
     *
     * @OA\Get(
     *     tags={"cart"},
     *     path="/store/api/v1/carts/{id}",
     *     operationId="getCart",
     *     summary = "Returns all the products added to a cart with given identifier, along with total sum.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="The cart ID", example="b0145a23-14db-4219-b02a-53de833e470d", @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *      response="200",
     *      description="A cart containing an array with added products",
     *      @OA\JsonContent(
     *          title="items",
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
     *      response="404",
     *      description="Cart with a given ID could not be found",
     *     ),
     *
     *     @OA\Response(
     *      response="400",
     *      description="Invalid ID parameter",
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
        $cartId = Validator::validateString('id', $request->getAttribute('id'));

        $cart = new Cart($this->getServiceContainer(), $cartId);
        if ($cart->isEmpty()) {
            throw new \Exception('Cart not found', 404);
        }

        return $cart->getJSON($this->getServer());
    }
}

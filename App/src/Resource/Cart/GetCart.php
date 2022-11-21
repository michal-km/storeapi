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
use App\Resource\Cart\CartToolsTrait;

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
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="The cart ID",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="A cart containing an array with added products",
     *     ),
     *     @OA\Response(
     *      response="404",
     *      description="Cart with a given ID could not be found",
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Invalid ID parameter",
     *     ),
     *    @OA\Response(
     *      response="500",
     *      description="Server error",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $cartId = $this->validateString('id', $request->getAttribute('id'));

        $cartItems = $this->getCartItems($cartId);
        if (count($cartItems) === null) {
            throw new \Exception('Cart not found', 404);
        }

        return $this->getCartJSON($cartId);
    }
}

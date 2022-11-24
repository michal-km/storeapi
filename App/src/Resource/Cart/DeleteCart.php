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
use App\Validator\Validator;
use App\Repository\Cart;

/**
 * DELETE /store/api/v1/carts/{id}
 */
final class DeleteCart extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Delete(
     *     tags={"cart"},
     *     path="/store/api/v1/carts/{id}",
     *     operationId="deleteCart",
     *     summary = "Removes the cart and its content.",
     *     description="Cart identifier can be used again, adding products to it will create a new cart.",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *     description="The cart ID", example="b0145a23-14db-4219-b02a-53de833e470d", @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *      response="200",
     *      description="Cart was deleted successfully",
     *     ),
     *
     *     @OA\Response(
     *      response="404",
     *      description="Cart not found",
     *     ),
     *
     *     @OA\Response(
     *      response="500",
     *      description="Server error",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $cartId = validator::validateString('id', $request->getAttribute('id'));

        $cart = new Cart($this->getEntityManager());
        $cart->load($cartId);
        if ($cart->items()->isEmpty()) {
            throw new \Exception('Cart not found', 404);
        }

        $cart->truncate();

        return [
            'status' => 'deleted',
        ];
    }
}

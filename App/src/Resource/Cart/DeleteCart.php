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
use App\Validator\Validator;
use App\Entity\Product;

/**
 * DELETE /store/api/v1/carts/{id}
 */
final class DeleteCart extends AbstractResourceHandler implements RequestHandlerInterface
{
    use CartToolsTrait;

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
     *     @OA\Parameter(name="id", in="path", required=true, description="The cart ID", example="b0145a23-14db-4219-b02a-53de833e470d", @OA\Schema(type="string")),
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
        $id = validator::validateString('id', $request->getAttribute('id'));
        $p = $this->getCartItems($id);
        if (count($p) == 0) {
            throw new \Exception("Cart not found", 404);
        }

        $em = $this->getEntityManager();

        foreach ($p as $item) {
            $em->remove($item);
        }
        $em->flush();

        $data = [
            'status' => 'deleted',
        ];
        return $data;
    }
}

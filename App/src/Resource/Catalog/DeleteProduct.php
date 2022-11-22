<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource\Catalog;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Resource\AbstractResourceHandler;
use App\Validator\Validator;
use App\Entity\Product;

/**
 * DELETE /catalog/api/v1/products/{id}
 */
final class DeleteProduct extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Delete(
     *     tags={"catalog"},
     *     path="/catalog/api/v1/products/{id}",
     *     operationId="deleteProduct",
     *     summary = "Removes the products from the catalog.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="The product ID", example="39", @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *      response="204",
     *      description="Empty response",
     *     ),
     *
     *     @OA\Response(
     *      response="404",
     *      description="Product not found",
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
        $this->authorize($request, "catalog administrator");
        $id = Validator::validateInteger('id', $request->getAttribute('id'));
        $em = $this->getEntityManager();
        $productRepository = $em->getRepository(Product::class);
        $p = $productRepository->find($id);
        if (!$p) {
            throw new \Exception("Product not found", 404);
        }

        $em->remove($p);
        $em->flush();

        $data = [
            'status' => 'deleted',
        ];
        return $data;
    }
}

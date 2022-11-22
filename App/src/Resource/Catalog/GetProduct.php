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
 * @OA\Server(url="http://localhost:8080")
 * @OA\Info(title="API", version="1.0")
 *
 * GET /catalog/api/v1/products/{id}
 */
final class GetProduct extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * @OA\Get(
     *     tags={"catalog"},
     *     path="/catalog/api/v1/products/{id}",
     *     operationId="getProduct",
     *     summary = "Returns all information about the single product.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="The product ID", example="39", @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *      response="200",
     *      description="Product object",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="id", ref="#/components/schemas/Product/properties/id"),
     *              @OA\Property(property="title", ref="#/components/schemas/Product/properties/Title"),
     *              @OA\Property(property="price", ref="#/components/schemas/Product/properties/Price"),
     *              @OA\Property(
     *                  property="link",
     *                  type="string",
     *                  example="http://localhost:8080/catalog/api/v1/product/39"
     *              ),
     *          ),
     *      )
     *     ),
     *
     *     @OA\Response(
     *      response="404",
     *      description="Product not found",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $id = Validator::validateInteger('id', $request->getAttribute('id'));
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $product = $productRepository->find($id);
        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        return [
            'product' => $product->getJSON($this->getServer() . 'catalog/api/v1/products/'),
        ];
    }
}

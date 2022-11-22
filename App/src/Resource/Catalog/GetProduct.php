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
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="The product ID",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Product object",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=39
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  example="Baldur's Gate"
     *              ),
     *              @OA\Property(
     *                  property="price",
     *                  type="number",
     *                  example=3.99
     *              ),
     *              @OA\Property(
     *                  property="link",
     *                  type="string",
     *                  example="http://localhost:8080/catalog/api/v1/product/39"
     *              ),
     *          ),
     *      )
     *     ),
     *     @OA\Response(
     *      response="404",
     *      description="Product not found",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $server = 'http://localhost:8080/';
        $id = $this->validateInteger('id', $request->getAttribute('id'));
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $p = $productRepository->find($id);
        if (!$p) {
            throw new \Exception("Product not found", 404);
        }
        $productData = [
            'id' => $p->getId(),
            'title' => $p->getTitle(),
            'price' => $p->getPrice() / 100,
            'link' => $server . 'catalog/api/v1/products/' . $p->getId(),
        ];

        $data = [
            'product' => $productData,
        ];
        return $data;
    }
}

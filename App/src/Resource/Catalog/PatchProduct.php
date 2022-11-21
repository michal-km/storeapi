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
 * PATCH /catalog/api/v1/products/{id}
 */
final class PatchProduct extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Patch(
     *     tags={"catalog"},
     *     path="/catalog/api/v1/products/{id}",
     *     operationId="patchProduct",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="The product ID",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            @OA\Property(
     *                property="title",
     *                type="string",
     *                format="string",
     *                example="A title",
     *                minLength=1,
     *                maxLength=255
     *            ),
     *            @OA\Property(
     *                property="price",
     *                type="number",
     *                example="6.99",
     *                minimum=0
     *            ),
     *         ),
     *      ),
     *     @OA\Response(
     *      response="200",
     *      description="Product was updated successfully",
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
     *    @OA\Response(
     *      response="304",
     *      description="Product was not changed",
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
     *      response="400",
     *      description="Invalid input data",
     *     ),
     *    @OA\Response(
     *      response="500",
     *      description="Server error",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "catalog administrator");
        $server = 'http://localhost:8080/';
        $em = $this->getEntityManager();
        $productRepository = $em->getRepository(Product::class);

        $id = $this->validateInteger('id', $request->getAttribute('id'));
        $product = $productRepository->find($id);
        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        $changes = 0;
        $params = $request->getParsedBody();
        if (isset($params['title'])) {
            $title = $this->validateString('title', $params['title']);
            if ($product->getTitle() !== $title) {
                $existingProduct = $productRepository->findOneBy(['Title' => $title]);
                if (null !== $existingProduct) {
                    throw new \Exception('This title is already taken', 400);
                }
                $product->setTitle($title);
                $changes++;
            }
        }
        if (isset($params['price'])) {
            $price = $this->validatePrice('price', $params['price']);
            if ($product->getPrice() !== $price) {
                $product->setPrice($price);
                $changes++;
            }
        }

        if ($changes > 0) {
            $em->persist($product);
            $em->flush();
        } else {
            throw new \Exception("Not changed", 304);
        }

        $productData = [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'price' => $product->getPrice() / 100,
            'link' => $server . 'catalog/api/v1/products/' . $product->getId(),
        ];

        $data = [
            'product' => $productData,
        ];
        return $data;
    }
}

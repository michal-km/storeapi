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
     *     summary = "Updates a product in the catalog.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="The product ID", example="39", @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            @OA\Property(property="title", type="string", format="string", example="Baldur's Gate", minLength=1, maxLength=255),
     *            @OA\Property(property="price", type="number", example=6.99, minimum=0),
     *         ),
     *      ),
     *
     *     @OA\Response(
     *      response="200",
     *      description="Product was updated successfully",
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
     *    @OA\Response(
     *      response="304",
     *      description="Product was not changed",
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
        $this->authorize($request, "catalog administrator");
        $em = $this->getEntityManager();
        $productRepository = $em->getRepository(Product::class);

        $id = Validator::validateInteger('id', $request->getAttribute('id'));
        $product = $productRepository->find($id);
        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        $changes = 0;
        $params = $request->getParsedBody();
        $title = $this->requireParameter($params, 'title', 'string', false);
        $price = $this->requireParameter($params, 'price', 'price', false);

        if ($title) {
            if ($product->getTitle() !== $title) {
                $existingProduct = $productRepository->findOneBy(['Title' => $title]);
                if (null !== $existingProduct) {
                    throw new \Exception('This title is already taken', 400);
                }
                $product->setTitle($title);
                $changes++;
            }
        }

        if ($price) {
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

        return [
            'product' => $product->getJSON($this->getServer() . 'catalog/api/v1/products/'),
        ];
    }
}

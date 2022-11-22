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
 * POST /catalog/api/v1/products
 */
final class PostProduct extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Post(
     *     tags={"catalog"},
     *     path="/catalog/api/v1/products",
     *     operationId="postProduct",
     *     summary = "Creates a new product in the catalog.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"title", "price"},
     *            @OA\Property(property="title", type="string", format="string", example="Baldur's Gate", minLength=1, maxLength=255),
     *            @OA\Property(property="price", type="number", example=6.99, minimum=0),
     *         ),
     *      ),
     *
     *     @OA\Response(
     *      response="201",
     *      description="Product was created successfully",
     *      @OA\JsonContent(
     *          type="array",
     *          title="product",
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

        $params = $request->getParsedBody();
        $title = $this->requireParameter($params, 'title', 'string');
        $price = $this->requireParameter($params, 'price', 'price');

        $em = $this->getEntityManager();
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $product = $productRepository->findOneBy(['Title' => $title]);
        if (null !== $product) {
            throw new \Exception('This title is already taken', 400);
        }

        $product = new Product($title, $price);
        $em->persist($product);
        $em->flush();
        if (null === $product->getId()) {
            print "NewId = null";
            throw new \Exception("Product could not be created", 500);
        }

        return [
            'product' => $product->getJSON($this->getServer() . 'catalog/api/v1/products/'),
            'code' => 201,
        ];
    }
}

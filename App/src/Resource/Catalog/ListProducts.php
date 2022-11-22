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
 * GET /catalog/api/v1/products
 */
final class ListProducts extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @OA\Get(
     *     tags={"catalog"},
     *     path="/catalog/api/v1/products",
     *     operationId="listProducts",
     *     summary = "Lists all the products from the catalog.",
     *     description = "Results are spli to pages with three items on each.
     *                    To fetch the first page, call the action without providing a cursor (or set it to 0).
     *                    To fetch next page, use link provided in meta/cursor.next field, or use cursor variable as ID
     *                    of the first product on the page.
     *                    If there are no more results, no link is provided.
     *                    If the cursor value is greater than the ID of any product, an empty set will be returned.",
     *
     *     @OA\Parameter(
     *          name="cursor",
     *          in="query",
     *          required=false,
     *          description="ID of the first product in result set",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="List products",
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
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $server = 'http://localhost:8080/';
        $pageSize = 3;
        $params = $request->getQueryParams();
        $cursor = (isset($params['cursor'])) ? $this->validateInteger('cursor', $params['cursor']) : 0;
        $productRepository = $this->getEntityManager()->getRepository(Product::class);

        // get product list
        $productList = [];
        $products = $productRepository->findBy([], [], $pageSize, $cursor);
        foreach ($products as $p) {
            $productList[] = [
                'id' => $p->getId(),
                'title' => $p->getTitle(),
                'price' => $p->getPrice() / 100,
                'link' => $server . 'catalog/api/v1/products/' . $p->getId(),
            ];
        }

        // find next product id
        $next = null;
        if (count($products) === 3) {
            if ($productRepository->findBy([], [], 1, $cursor + $pageSize)) {
                $next = $cursor + $pageSize;
            }
        }

        // get product count
        $totalProducts = $productRepository->createQueryBuilder('product')
            ->select('count(product.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = [
            'products' => $productList,
            'meta' => [
                'total.count' => $totalProducts,
                //'post' => $request->getParsedBody(),
            ],
        ];
        if ($next) {
            $data['meta']['cursor.next'] = $server . 'catalog/api/v1/products?cursor=' . $next;
        }
        return $data;
    }
}

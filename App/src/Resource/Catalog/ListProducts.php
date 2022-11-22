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
     *     description = "Results are spli to pages with three items on each. To fetch the first page, call the action without providing a cursor (or set it to 0). To fetch next page, use link provided in meta/cursor.next field, or use cursor variable as ID of the first product on the page. If there are no more results, no link is provided. If the cursor value is greater than the ID of any product, an empty set will be returned.",
     *
     *     @OA\Parameter(name="cursor", in="query", required=false, description="ID of the first product in result set. This parameter is optional. For the first page, use 0 or not provide value at all.", @OA\Schema( type="integer")),

     *     @OA\Response(
     *      response="200",
     *      description="List products",
     *      @OA\JsonContent(
     *          @OA\Property(
     *                  type="array",
     *                  title="products",
     *                  property="products",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/Product/properties/id"),
     *                      @OA\Property(property="title", ref="#/components/schemas/Product/properties/Title"),
     *                      @OA\Property(property="price", ref="#/components/schemas/Product/properties/Price"),
     *                      @OA\Property(property="link", type="string", example="http://localhost:8080/catalog/api/v1/product/39"),
     *                  ),
     *              ),
     *          @OA\Property(
     *              title="meta",
     *              property="meta",
     *              @OA\Property(property="total.count", type="number", example="1"),
     *              @OA\Property(property="cursor.next", type="string", example="http://localhost:8080/catalog/api/v1/product/39"),
     *              ),
     *          ),
     *     ),
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $pageSize = 3;
        $params = $request->getQueryParams();
        $cursor = (isset($params['cursor'])) ? Validator::validateInteger('cursor', $params['cursor']) : 0;
        $productRepository = $this->getEntityManager()->getRepository(Product::class);

        // get product list
        $productList = [];
        $products = $productRepository->findBy([], [], $pageSize, $cursor);
        foreach ($products as $p) {
            $productList[] = $p->getJSON($this->getServer() . 'catalog/api/v1/products/');
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
            ],
        ];
        if ($next) {
            $data['meta']['cursor.next'] = $this->getServer() . 'catalog/api/v1/products?cursor=' . $next;
        }
        return $data;
    }
}

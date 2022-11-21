<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Functional\Catalog;

use App\Tests\Functional\FunctionalTestCase;
use App\Entity\Product;

class PutCartTest extends FunctionalTestCase
{
    public function testAddProductToCart(): void
    {
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $postData = [
            [
                'id' => 39,
                'quantity' => 5,
            ],
            [
                'id' => 38,
                'quantity' => 2,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 201);

        // is the response valid JSON?
        $data = (string) $response->getBody();
        $this->assertJson($data);

        // has the response all the neccessary fields?
        $arr = json_decode($data, true);
        $this->assertArrayHasKey('meta', $arr);
        $this->assertArrayHasKey('cart.id', $arr['meta']);
        $this->assertArrayHasKey('cart.total', $arr['meta']);
        $this->assertSame(32.93, $arr['meta']['cart.total']);
        $this->assertArrayHasKey('items', $arr);
        $items = $arr['items'];
        $this->assertSame(2, count($arr['items']));
        foreach ($items as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('quantity', $item);
            $this->assertGreaterThan(0, $item['quantity']);
            // is the product in the database?
            $productFound = $productRepository->find($item['id']);
            $this->assertNotNull($productFound);
        }

        $cartId = $arr['meta']['cart.id'];

        // try to add more items to the same cart
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $postData = [
            [
                'id' => 39,
                'quantity' => 4,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts/' . $cartId);
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testAddNotExistantProduct(): void
    {
        $postData = [
            [
                'id' => 9999,
                'quantity' => 5,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 304);
    }

    public function testAddTooMuch(): void
    {
        $postData = [
            [
                'id' => 39,
                'quantity' => 7,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        $this->assertSame($response->getStatusCode(), 201);

        $data = (string) $response->getBody();
        $arr = json_decode($data, true);
        $cartId = $arr['meta']['cart.id'];

        $request = $this->createRequest('PUT', '/store/api/v1/carts/' . $cartId);
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        $this->assertSame($response->getStatusCode(), 304);

        // try to add more than three products
        $postData = [
            [
                'id' => 38,
                'quantity' => 2,
            ],
            [
                'id' => 37,
                'quantity' => 5,
            ],
            [
                'id' => 36,
                'quantity' => 8,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
    }

    public function testRemoveProduct(): void
    {
        $postData = [
            [
                'id' => 39,
                'quantity' => 5,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 201);

        $data = (string) $response->getBody();
        $arr = json_decode($data, true);
        $cartId = $arr['meta']['cart.id'];

        // try to remove some items
        $postData = [
            [
                'id' => 39,
                'quantity' => -2,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts/' . $cartId);
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        $this->assertSame($response->getStatusCode(), 200);
        $data = (string) $response->getBody();
        $arr = json_decode($data, true);
        $expectedData = [
            [
                'id' => 39,
                'quantity' => 3,
                'link' => 'http://localhost:8080/catalog/api/v1/products/39',
            ],
        ];
        $this->assertSame($expectedData, $arr['items']);

        // try to remove more than available
        $postData = [
            [
                'id' => 39,
                'quantity' => -99,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts/' . $cartId);
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        $this->assertSame($response->getStatusCode(), 200);
        $data = (string) $response->getBody();
        $arr = json_decode($data, true);
        $expectedData = [];
        $this->assertSame($expectedData, $arr['items']);
        $this->assertArrayHasKey('cart.id', $arr['meta']);
    }

    public function testAddProductWithInvalidData(): void
    {
        $postData = [
            [
                'id' => 'aaa',
                'quantity' => 7,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            [
                'id' => 39,
                'quantity' => 'aaa',
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            [
                'id' => 39,
                'quantity' => -100,
            ],
        ];
        $request = $this->createRequest('PUT', '/store/api/v1/carts');
        $request = $request->withParsedBody(['items' => $postData]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 201);
    }
}

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

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Response;
use App\Tests\Functional\FunctionalTestCase;

class ProductListTest extends FunctionalTestCase
{
    public function testGetProductList(): void
    {
        $request = $this->createRequest('GET', '/catalog/api/v1/products');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);
        $data = (string) $response->getBody();

        // is the response valid JSON?
        $this->assertJson($data);

        // has the response product array?
        $arr = json_decode($data, true);
        $this->assertArrayHasKey('products', $arr);

        // no more than 3 products per page?
        $products = $arr['products'];
        $productCount = count($products);
        $this->assertGreaterThanOrEqual(0, $productCount);
        $this->assertLessThanOrEqual(3, $productCount);

        // is there meta section?
        $this->assertArrayHasKey('meta', $arr);

        // is there total.count == 5?
        $this->assertArrayHasKey('total.count', $arr['meta']);
        $this->assertSame(5, $arr['meta']['total.count']);

        // is there a cursor for the next page?
        $this->assertArrayHasKey('cursor.next', $arr['meta']);

        // is the cursor callable?
        $uri = parse_url($arr['meta']['cursor.next']);
        $params = [];
        parse_str($uri['query'], $params);
        $request = $this->createRequest('GET', $uri['path'])->withQueryParams($params);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 200);
        $data = (string) $response->getBody();
        $this->assertJson($data);
        $arr = json_decode($data, true);
        $this->assertArrayHasKey('products', $arr);
        $this->assertArrayHasKey('meta', $arr);

        // there should be no cursor on the second page
        $this->assertArrayNotHasKey('cursor.next', $arr['meta']);
    }

    public function testGetProductListWithInvalidCursorValue()
    {
        // check for invalid cursor value
        $cursor = 'aaa';
        $request = $this->createRequest('GET', '/catalog/api/v1/products')->withQueryParams(['cursor' => $cursor]);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $cursor = '-1';
        $request = $this->createRequest('GET', '/catalog/api/v1/products')->withQueryParams(['cursor' => $cursor]);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $cursor = "0'";
        $request = $this->createRequest('GET', '/catalog/api/v1/products')->withQueryParams(['cursor' => $cursor]);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $cursor = "1.67'";
        $request = $this->createRequest('GET', '/catalog/api/v1/products')->withQueryParams(['cursor' => $cursor]);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);
    }
}

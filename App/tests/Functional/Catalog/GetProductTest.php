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
use SebastianBergmann\Type\VoidType;
use Slim\App;
use Slim\Psr7\Response;
use App\Tests\Functional\FunctionalTestCase;
use Slim\Exception\HttpNotFoundException;

class GetProductTest extends FunctionalTestCase
{
    public function testGetProduct(): void
    {
        $id = 36;
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);

        // is the response valid JSON?
        $data = (string) $response->getBody();
        $this->assertJson($data);

        // has the response all the neccessary fields?
        $arr = json_decode($data, true);
        $this->assertArrayHasKey('product', $arr);
        $product = $arr['product'];
        $this->assertArrayHasKey('id', $product);
        $this->assertArrayHasKey('title', $product);
        $this->assertArrayHasKey('price', $product);
        $this->assertArrayHasKey('link', $product);
    }

    /**
     * @expectedException HttpNotFoundException
     */
    public function testGetProductWithInvalidId(): void
    {
        $id = '';
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 404);

        $id = null;
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 404);

        $id = 555;
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 404);

        $id = -1;
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 404);

        $id = 'aaa';
        $request = $this->createRequest('GET', '/catalog/api/v1/products/' . $id);
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);
    }
}

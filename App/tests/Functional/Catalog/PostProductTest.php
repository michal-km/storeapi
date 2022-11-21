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

class PostProductTest extends FunctionalTestCase
{
    public function testPostNewProduct(): void
    {
        $postData = [
            'title' => 'Settlers 3',
            'price' => '6.99',
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 201);

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

        // is the product in the database?
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $productFound = $productRepository->findOneBy(['Title' => 'Settlers 3']);
        $this->assertSame($product['id'], $productFound->getId());
    }

    public function testPostProductWithInvalidData(): void
    {
        $postData = [
            'title' => 'Settlers 3',
            'price' => 'aaaaa',
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            'title' => '',
            'price' => 12,
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            'title' => 'jkfjreklqgjqerlkgjrelkgvjrklegjrelkqghjkrgfjrkgrjkeqghrjkfgherjkghrej
                        kghrtjekqghertjklghrjkelqfhwerjklfghrjkghrejkqglherghfgrgregregergfwr
                        gregreregfdgdfagvfdsgdfsgrdegrefdsFDEFEWFDsdcsfewrfgwerfeewfwfr EFRWE
                        jhklhuiklhfjkcwawerhfjkrhgfjkrhegjkerthkghvnfjkdvgherjkfghjkrehgjkreh
                        fsdejkfhjklhjklhjklhfjkehwferfgwergrkw  ghlfjrkfhjkwerh fjkrehgregreq
                        few gkfghrjlfghjrkweghrjkefhjKWHERJKFHFJKHEWJKFDHEWFMSDB    GjhgjNJHK
                        hjhjkhjkhjklhjkgregrrjl;eg5roigjhkfrldegjrlkegjrlkejghlkt.ejhkglterjj',
            'price' => 45,
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            'title' => 'Settlers 3',
            'price' => -10,
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testPostProductWithDuplicateTitle(): void
    {
        $postData = [
            'title' => 'Settlers 3',
            'price' => '6.99',
        ];
        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        // first time OK
        $this->assertSame($response->getStatusCode(), 201);

        $request = $this->createRequest('POST', '/catalog/api/v1/products');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        // second time should fail
        $this->assertSame($response->getStatusCode(), 400);
    }
}

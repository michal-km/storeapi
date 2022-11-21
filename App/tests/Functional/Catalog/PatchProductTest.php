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

class PatchProductTest extends FunctionalTestCase
{
    public function testPatchProduct(): void
    {
        $postData = [
            'title' => 'Cyberpunk 2077',
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);

        // try again with the same data
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 304);

        $postData = [
            'price' =>  10.99,
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);

        // try again with the same data
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 304);

        // change all fields
        $postData = [
            'title' => 'Syberia 2',
            'price' => 2.88,
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
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

        // is the product in the database?
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $productFound = $productRepository->find(39);
        $this->assertSame('Syberia 2', $productFound->getTitle());
        $this->assertSame(288, $productFound->getPrice());
    }

    public function testPatchProductWithInvalidData(): void
    {
        $postData = [
            'title' => 'Settlers 3',
            'price' => 'aaaaa',
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            'title' => '',
            'price' => 12,
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
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
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);

        $postData = [
            'title' => 'Settlers 3',
            'price' => -10,
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testPatchProductWithDuplicateTitle(): void
    {
        $postData = [
            'title' => 'Fallout',
            'price' => '6.99',
        ];
        $request = $this->createRequest('PATCH', '/catalog/api/v1/products/39');
        $request = $request->withParsedBody($postData);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->getAppInstance()->handle($request);
        $this->assertSame($response->getStatusCode(), 400);
    }
}

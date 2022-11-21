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

class DeleteProductTest extends FunctionalTestCase
{
    public function testDeleteProduct(): void
    {
        $request = $this->createRequest('DELETE', '/catalog/api/v1/products/39');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);

        // is product deleted?
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        $product = $productRepository->find(39);
        $this->assertNull($product);

        // try deleting again
        $request = $this->createRequest('DELETE', '/catalog/api/v1/products/39');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 404);
    }
}

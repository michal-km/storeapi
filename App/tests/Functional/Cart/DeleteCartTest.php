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
use App\Entity\CartItem;

class DeleteCartTest extends FunctionalTestCase
{
    public function testDeleteCart(): void
    {
        $request = $this->createRequest('DELETE', '/store/api/v1/carts/cfe30122-74a6-4cf8-bb02-3522abf790a0');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 200);

        // is cart deleted?
        $cartRepository = $this->getEntityManager()->getRepository(CartItem::class);
        $cart = $cartRepository->find(1);
        $this->assertNull($cart);

        // try deleting again
        $request = $this->createRequest('DELETE', '/store/api/v1/carts/cfe30122-74a6-4cf8-bb02-3522abf790a0');
        $response = $this->getAppInstance()->handle($request);

        // check return code
        $this->assertSame($response->getStatusCode(), 404);
    }
}

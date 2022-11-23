<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Unit;

use Doctrine\ORM\EntityManager;
use App\Repository\Cart;
use PHPUnit\Framework\TestCase;

final class PutCartTest extends TestCase
{
    public function testGetCartId(): void
    {
        $em = $this->createStub(EntityManager::class);
        $cart = new Cart($em);
        $cartId = $cart->getCartId(null);
        $this->assertNotNull($cartId);
    }
}

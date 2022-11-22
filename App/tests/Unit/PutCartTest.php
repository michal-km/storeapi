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

use Psr\Container\ContainerInterface;
use App\Resource\Cart\PutCart;
use PHPUnit\Framework\TestCase;

final class PutCartTest extends TestCase
{
    public function testGetCartId(): void
    {
        $em = $this->createStub(ContainerInterface::class);
        $pc = new PutCart($em);
        $cartId = $pc->getCartId(null);
        $this->assertNotNull($cartId);
    }
}

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

use PHPUnit\Framework\TestCase;
use App\Repository\Collection;

class CollectionTest extends TestCase
{
    public function testCreate(): void
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
    }
}

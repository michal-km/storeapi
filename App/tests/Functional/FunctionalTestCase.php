<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Request;
use App\Controller\ProductApiController;
use App\Controller\CartApiController;
use Slim\Psr7\Uri;
use Slim\Psr7\Headers;
use Slim\Psr7\Factory\StreamFactory;
use Doctrine\ORM\EntityManager;
use UMA\DIC\Container;
use App\Kernel;

class FunctionalTestCase extends TestCase
{
    private App $app;
    private EntityManager $entityManager;

    protected function getAppInstance(): App
    {
        return $this->app;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $serverParams = [],
        array $cookies = [],
    ): Request {
        $uri = new Uri('http://', 'localhost', 8080, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new Request($method, $uri, $h, $cookies, $serverParams, $stream);
    }

    protected function setUp(): void
    {
        $kernel = new Kernel(__DIR__ . '/../../settings.test.php');
        $cnt = $kernel->getServiceContainer();
        $this->app = $cnt->get(App::class);
        $this->entityManager = $cnt->get(EntityManager::class);
        $cnt->get(ProductApiController::class);
        $cnt->get(CartApiController::class);
        $this->getEntityManager()->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->getEntityManager()->getConnection()->rollBack();
    }
}

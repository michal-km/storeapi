<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service;

use Slim\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\ORM\EntityManager;

abstract class AbstractController
{
    protected App $app;

    public function __construct(private ContainerInterface $serviceContainer)
    {
        $this->app = $serviceContainer->get(App::class);
        $this->run();
    }

    public function getServiceContainer(): ContainerInterface
    {
        return $this->serviceContainer;
    }

    abstract public function run();

    protected function registerHandler(string $className, array $methods, string $uri)
    {
        $this->getServiceContainer()->set(
            $className,
            static function (ContainerInterface $c) use ($className): RequestHandlerInterface {
                $entityManager = $c->get(EntityManager::class);
                return new $className($entityManager);
            }
        );
        $this->app->map($methods, $uri, $className);
    }
}

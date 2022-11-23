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

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;
use Slim\Handlers\Strategies\RequestHandler;
use App\Controller\ProductApiController;
use App\Controller\CartApiController;
use App\Controller\SwaggerController;
use App\Repository\Cart;

final class ServiceRegistry implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(App::class, static function (ContainerInterface $c): App {
            $settings = $c->get('settings');

            $app = AppFactory::create(null, $c);

            $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails']
            );

            $app->add(new ContentLengthMiddleware());

            $routeCollector = $app->getRouteCollector();
            $routeCollector->setDefaultInvocationStrategy(new RequestHandler(true));

            return $app;
        });

        $c->set(ProductApiController::class, static function (ContainerInterface $c): ProductApiController {
            return new ProductApiController($c);
        });

        $c->set(CartApiController::class, static function (ContainerInterface $c): CartApiController {
            return new CartApiController($c);
        });

        $c->set(SwaggerController::class, static function (ContainerInterface $c): SwaggerController {
            return new SwaggerController($c);
        });
    }
}

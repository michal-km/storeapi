<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

use Slim\App;
use UMA\DIC\Container;
use App\Controller\ProductApiController;
use App\Controller\CartApiController;
use App\Controller\SwaggerController;
use App\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Container $cnt */
$kernel = new Kernel(__DIR__ . '/../settings.php');
$cnt = $kernel->getServiceContainer();

/** @var App $app */
$app = $cnt->get(App::class);
$cnt->get(ProductApiController::class);
$cnt->get(CartApiController::class);
$cnt->get(SwaggerController::class);
$app->run();

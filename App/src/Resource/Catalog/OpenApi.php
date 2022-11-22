<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource\Catalog;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Resource\AbstractResourceHandler;

use function json_encode;

/**
 * GET /catalog/openapi
 */
final class OpenApi extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $swagger = \OpenApi\Generator::scan([__DIR__, , __DIR__ . '../../Entity']);
        return $swagger;
    }
}

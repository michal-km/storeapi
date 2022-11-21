<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Resource\AbstractResourceHandler;

use function json_encode;

/**
 * Action handler providing basic info on '/' url.
 */
final class Index extends AbstractResourceHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        return 'API Version: 1.0';
    }
}

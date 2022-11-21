<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Service\AbstractController;

/**
 * Registers action handlers for the API endpoints.
 */
class SwaggerController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->registerHandler("\App\Resource\Cart\OpenApi", ["GET"], "/cart/openapi");
        $this->registerHandler("\App\Resource\Catalog\OpenApi", ["GET"], "/catalog/openapi");
        $this->registerHandler("\App\Resource\Index", ["GET"], "/");
    }
}

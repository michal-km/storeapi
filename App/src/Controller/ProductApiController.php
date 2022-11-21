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
class ProductApiController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->registerHandler("\App\Resource\Catalog\ListProducts", ["GET"], "/catalog/api/v1/products");
        $this->registerHandler("\App\Resource\Catalog\GetProduct", ["GET"], "/catalog/api/v1/products/{id}");
        $this->registerHandler("\App\Resource\Catalog\PostProduct", ["POST"], "/catalog/api/v1/products");
        $this->registerHandler("\App\Resource\Catalog\DeleteProduct", ["DELETE"], "/catalog/api/v1/products/{id}");
        $this->registerHandler("\App\Resource\Catalog\PatchProduct", ["PATCH"], "/catalog/api/v1/products/{id}");
    }
}

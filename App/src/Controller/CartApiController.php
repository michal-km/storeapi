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
class CartApiController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->registerHandler("\App\Resource\Cart\GetCart", ["GET"], "/store/api/v1/carts/{id}");
        $this->registerHandler("\App\Resource\Cart\PutCart", ["PUT"], "/store/api/v1/carts/{id}");
        $this->registerHandler("\App\Resource\Cart\PutNewCart", ["PUT"], "/store/api/v1/carts");
        $this->registerHandler("\App\Resource\Cart\DeleteCart", ["DELETE"], "/store/api/v1/carts/{id}");
    }
}

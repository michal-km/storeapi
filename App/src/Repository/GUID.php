<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

class GUID
{
    /**
     * Creates a Globally Unique Identifier.
     *
     * @param mixed $idParam If provided, the function will check for its validity.
     *
     * @return string Returns a GUID (either $id if it is a valid GUID  or a new one).
     */
    public static function create(?string $id = null): string
    {
        if ($id && preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $id)) {
            return $id;
        }

        if (function_exists('com_create_guid') === true) {
            $cartId = trim(com_create_guid(), '{}');
        } else {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $cartId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        return $cartId;
    }
}

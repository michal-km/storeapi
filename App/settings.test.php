<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

return [
    'settings' => [
        'slim' => [
            'displayErrorDetails' => false,
            'logErrors' => false,
            'logErrorDetails' => false,
        ],

        'doctrine' => [
            'dev_mode' => false,
            'cache_dir' => __DIR__ . '/var/doctrine',
            'metadata_dirs' => [__DIR__ . '/src/Domain'],
            'connection' => [
                'driver' => 'pdo_mysql',
                'host' => 'database',
                'port' => 3306,
                'dbname' => 'store_test',
                'user' => 'root',
                'password' => 'UltraSecretRootPassword',
                'charset' => 'utf8mb4'
            ]
        ]
    ]
];

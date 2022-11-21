<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UMA\DIC\Container;
use App\Service\Doctrine;
use App\Service\ServiceRegistry;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Main application bootstrap logic
 */
class Kernel
{
    private Container $container;

    /**
     * @param string $settingsPath Path to setting.php file.
     */
    public function __construct(string $settingsPath)
    {
        $this->container = new Container(require $settingsPath);

        $this->container->set(EntityManager::class, static function (Container $c): EntityManager {
            $settings = $c->get('settings');

            $cache = $settings['doctrine']['dev_mode'] ?
            DoctrineProvider::wrap(new ArrayAdapter()) :
            DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']));

            $config = Setup::createAttributeMetadataConfiguration(
                $settings['doctrine']['metadata_dirs'],
                $settings['doctrine']['dev_mode'],
                null,
                $cache
            );

            return EntityManager::create($settings['doctrine']['connection'], $config);
        });

        $this->container->register(new Doctrine());
        $this->container->register(new ServiceRegistry());
    }

    /**
     * Returns service container.
     *
     * @return Container Service container instance.
     */
    public function getServiceContainer(): Container
    {
        return $this->container;
    }
}

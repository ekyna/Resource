<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Manager;

use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\OrmExtension;
use Ekyna\Component\Resource\Manager\AdapterInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Class ManagerFactoryAdapter
 * @package Ekyna\Component\Resource\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ManagerFactoryAdapter implements AdapterInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function createManager(ResourceConfig $config): ResourceManagerInterface
    {
        $managerClass = $config->getManagerClass();

        /** @var ResourceManagerInterface $manager */
        $manager = new $managerClass();

        /** @noinspection PhpParamsInspection */
        $manager->setWrapped($this->registry->getManagerForClass($config->getEntityClass()));

        return $manager;
    }

    public function supports(ResourceConfig $config): bool
    {
        return $config->getDriver() === OrmExtension::DRIVER;
    }
}

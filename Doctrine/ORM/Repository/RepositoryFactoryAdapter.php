<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\OrmExtension;
use Ekyna\Component\Resource\Repository\AdapterInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class RepositoryFactoryAdapter
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RepositoryFactoryAdapter implements AdapterInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function createRepository(ResourceConfig $config): ResourceRepositoryInterface
    {
        $repositoryClass = $config->getRepositoryClass();

        /** @var ResourceRepository $repository */
        $repository = new $repositoryClass();

        /** @noinspection PhpParamsInspection */
        $repository->setWrapped($this->registry->getRepository($config->getEntityClass()));

        return $repository;
    }

    public function supports(ResourceConfig $config): bool
    {
        return $config->getDriver() === OrmExtension::DRIVER;
    }
}

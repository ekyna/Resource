<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Factory;

use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\OrmExtension;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Factory\AdapterInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;

/**
 * Class FactoryFactoryAdapter
 * @package Ekyna\Component\Resource\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FactoryFactoryAdapter implements AdapterInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function createFactory(ResourceConfig $config): ResourceFactoryInterface
    {
        $factoryClass = $config->getFactoryClass();

        /** @var ResourceFactoryInterface $factory */
        $factory = new $factoryClass();

        if (!$factory instanceof ResourceFactory) {
            throw new UnexpectedTypeException($factory, ResourceFactory::class);
        }

        $factory->setManagerRegistry($this->registry);

        return $factory;
    }

    public function supports(ResourceConfig $config): bool
    {
        return $config->getDriver() === OrmExtension::DRIVER;
    }
}

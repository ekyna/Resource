<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Factory;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareTrait;
use Psr\Container\ContainerInterface;

use function get_class;
use function sprintf;

/**
 * Class FactoryFactory
 * @package Ekyna\Component\Resource\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FactoryFactory implements FactoryFactoryInterface
{
    use LocaleProviderAwareTrait;

    private ResourceRegistryInterface $registry;
    private ContainerInterface        $services;

    /** @var array<AdapterInterface> */
    private array $adapters = [];
    /** @var ResourceFactoryInterface[] */
    private array $factories = [];

    public function __construct(ResourceRegistryInterface $registry, ContainerInterface $services)
    {
        $this->registry = $registry;
        $this->services = $services;
    }

    public function registerAdapter(AdapterInterface $adapter): void
    {
        $class = get_class($adapter);

        if (isset($this->adapters[$class])) {
            throw new RuntimeException("Manager factory adapter $class is already registered.");
        }

        $this->adapters[$class] = $adapter;
    }

    public function getFactory(string $resource): ResourceFactoryInterface
    {
        $config = $this->registry->find($resource);

        $resourceClass = $config->getEntityClass();

        if (isset($this->factories[$resourceClass])) {
            return $this->factories[$resourceClass];
        }

        if ($this->services->has($resourceClass)) {
            return $this->factories[$resourceClass] = $this->services->get($resourceClass);
        }

        $factory = $this->createFactory($config);

        // Configure resource class
        $factory->setClass($resourceClass);

        // Inject locale provider if needed
        if ($factory instanceof LocaleProviderAwareInterface) {
            $factory->setLocaleProvider($this->getLocaleProvider());
        }

        return $this->factories[$resourceClass] = $factory;
    }

    private function createFactory(ResourceConfig $config): ResourceFactoryInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($config)) {
                return $adapter->createFactory($config);
            }
        }

        throw new LogicException(sprintf('Driver \'%s\' is not supported.', $config->getDriver()));
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Manager;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

use function get_class;
use function sprintf;

/**
 * Class ManagerFactory
 * @package Ekyna\Component\Resource\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ManagerFactory implements ManagerFactoryInterface
{
    private ResourceRegistryInterface        $registry;
    private ContainerInterface               $services;
    private ResourceEventDispatcherInterface $dispatcher;
    private bool                             $debug;

    /** @var array<AdapterInterface> */
    private array $adapters = [];
    /** @var ResourceManagerInterface[] */
    private array $managers = [];

    public function __construct(
        ResourceRegistryInterface        $registry,
        ContainerInterface               $services,
        ResourceEventDispatcherInterface $dispatcher,
        bool                             $debug
    ) {
        $this->registry = $registry;
        $this->services = $services;
        $this->dispatcher = $dispatcher;
        $this->debug = $debug;
    }

    public function registerAdapter(AdapterInterface $adapter): void
    {
        $class = get_class($adapter);

        if (isset($this->adapters[$class])) {
            throw new RuntimeException("Manager factory adapter $class is already registered.");
        }

        $this->adapters[$class] = $adapter;
    }

    public function registerManager(string $resource, ResourceManagerInterface $manager): void
    {
        if (isset($this->managers[$resource])) {
            throw new RuntimeException("A manager is already registered for class $resource.");
        }

        $this->managers[$resource] = $manager;
    }

    public function getManager(string $resource): ResourceManagerInterface
    {
        $config = $this->registry->find($resource);

        $resourceClass = $config->getEntityClass();

        if (isset($this->managers[$resourceClass])) {
            return $this->managers[$resourceClass];
        }

        if ($this->services->has($resourceClass)) {
            return $this->managers[$resourceClass] = $this->services->get($resourceClass);
        }

        $manager = $this->createManager($config);

        // Configure
        $manager->configure($resourceClass, $config->getId(), $this->debug);

        // Inject event dispatcher
        $manager->setDispatcher($this->dispatcher);

        return $this->managers[$resourceClass] = $manager;
    }

    private function createManager(ResourceConfig $config): ResourceManagerInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($config)) {
                return $adapter->createManager($config);
            }
        }

        throw new LogicException(sprintf(
            'Driver \'%s\' is not supported for resource \'%s\'.',
            $config->getDriver(),
            $config->getId()
        ));
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Cache\ResultCacheAwareInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Cache\ResultCacheAwareTrait;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Locale;
use Psr\Container\ContainerInterface;

use function get_class;
use function sprintf;

/**
 * Class RepositoryFactory
 * @package Ekyna\Component\Resource\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RepositoryFactory implements RepositoryFactoryInterface
{
    use Locale\LocaleProviderAwareTrait;
    use ResultCacheAwareTrait;

    private ResourceRegistryInterface $registry;
    private ContainerInterface        $services;

    /** @var array<AdapterInterface> */
    private array $adapters = [];
    /** @var ResourceRepositoryInterface[] */
    private array $repositories = [];

    public function __construct(ResourceRegistryInterface $registry, ContainerInterface $services)
    {
        $this->registry = $registry;
        $this->services = $services;
    }

    public function registerAdapter(AdapterInterface $adapter): void
    {
        $class = get_class($adapter);

        if (isset($this->adapters[$class])) {
            throw new RuntimeException("Repository factory adapter $class is already registered.");
        }

        $this->adapters[$class] = $adapter;
    }

    public function getRepository(string $resource): ResourceRepositoryInterface
    {
        $config = $this->registry->find($resource);

        $resourceClass = $config->getEntityClass();

        if (isset($this->repositories[$resourceClass])) {
            return $this->repositories[$resourceClass];
        }

        if ($this->services->has($resourceClass)) {
            return $this->repositories[$resourceClass] = $this->services->get($resourceClass);
        }

        $repository = $this->createRepository($config);

        // Inject result cache if needed
        if ($repository instanceof ResultCacheAwareInterface) {
            $repository->setResultCache($this->getResultCache());
        }

        // Inject locale provider if needed
        if ($repository instanceof Locale\LocaleProviderAwareInterface) {
            $repository->setLocaleProvider($this->getLocaleProvider());
        }

        // Translatable repository configuration
        if ($repository instanceof TranslatableRepositoryInterface) {
            $repository->setTranslatableFields($config->getTranslationFields());
        }

        return $this->repositories[$resourceClass] = $repository;
    }

    private function createRepository(ResourceConfig $config): ResourceRepositoryInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($config)) {
                return $adapter->createRepository($config);
            }
        }

        throw new LogicException(sprintf('Driver \'%s\' is not supported.', $config->getDriver()));
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Elastica;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\SearchRepositoryFactoryInterface;
use Ekyna\Component\Resource\Search\SearchRepositoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class SearchRepositoryFactory
 * @package Ekyna\Component\Resource\Bridge\Symfony\Elastica
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SearchRepositoryFactory implements SearchRepositoryFactoryInterface, Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    private ResourceRegistryInterface $resourceRegistry;
    private ContainerInterface        $repositories;
    private ContainerInterface        $indexes;
    private ContainerInterface        $transformers;
    private array                     $config;

    private array $cache = [];

    public function __construct(
        ResourceRegistryInterface $resourceRegistry,
        ContainerInterface $repositories,
        ContainerInterface $indexes,
        ContainerInterface $transformers,
        array $config
    ) {
        $this->resourceRegistry = $resourceRegistry;
        $this->repositories = $repositories;
        $this->indexes = $indexes;
        $this->transformers = $transformers;
        $this->config = $config;
    }

    public function getRepository(string $name): SearchRepositoryInterface
    {
        $name = $this->resourceRegistry->alias($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if ($this->repositories->has($name)) {
            return $this->cache[$name] = $this->repositories->get($name);
        }

        if (!isset($this->config[$name])) {
            throw new RuntimeException("No repository registered for class $name.");
        }

        $repositoryClass = $this->config[$name];

        /** @var SearchRepositoryInterface $repository */
        $repository = new $repositoryClass();

        if ($repository instanceof SearchRepository) {
            $repository->setSearchable($this->indexes->get($name));
            $repository->setTransformer($this->transformers->get($name));
        }

        // Inject locale provider if needed
        if ($repository instanceof Locale\LocaleProviderAwareInterface) {
            $repository->setLocaleProvider($this->getLocaleProvider());
        }

        return $this->cache[$name] = $repository;
    }
}

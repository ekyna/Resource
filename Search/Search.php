<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Search;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_slice;
use function in_array;
use function usort;

/**
 * Class Search
 * @package Ekyna\Bundle\CmsBundle\Search\Wide
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Search
{
    private const CACHE_KEY = 'search_choices.';

    private ResourceRegistryInterface        $registry;
    private SearchRepositoryFactoryInterface $factory;
    private array                            $resources;
    private ?CacheItemPoolInterface          $cache;


    public function __construct(
        ResourceRegistryInterface $registry,
        SearchRepositoryFactoryInterface $factory,
        array $resources,
        ?CacheItemPoolInterface $cache
    ) {
        $this->registry = $registry;
        $this->factory = $factory;
        $this->resources = $resources;
        $this->cache = $cache;
    }

    /**
     * Returns whether a repository is registered for the given resource name.
     */
    public function hasRepository(string $name): bool
    {
        $name = $this->registry->find($name)->getEntityClass();

        return array_key_exists($name, $this->resources);
    }

    /**
     * Returns the repository for the given name.
     */
    public function getRepository(string $name): SearchRepositoryInterface
    {
        if (!$this->hasRepository($name)) {
            throw new InvalidArgumentException("No repository registered for name '$name'.");
        }

        return $this->factory->getRepository($name);
    }

    /**
     * Returns the searchable resources ids.
     *
     * @param bool $global
     *
     * @return string[]
     */
    public function getChoices(bool $global = false): array
    {
        $key = self::CACHE_KEY . ($global ? 'global' : 'default');
        if ($this->cache && $this->cache->hasItem($key)) {
            $item = $this->cache->getItem($key);
            if ($item->isHit()) {
                return $item->get();
            }
        }

        if ($global) {
            $resources = array_keys(array_filter($this->resources, function ($global) {
                return $global;
            }));
        } else {
            $resources = array_keys($this->resources);
        }

        $choices = [];
        foreach ($resources as $class) {
            $config = $this->registry->find($class);
            $choices[$config->getId()] = [$config->getResourceLabel(true), $config->getTransDomain()];
        }

        if ($this->cache) {
            $item = $this->cache->getItem($key);
            $item->set($choices);
            $this->cache->save($item);
        }

        return $choices;
    }

    /**
     * Searches across all resources (having a search provider).
     *
     * @param Request $request
     *
     * @return array|Result[]
     */
    public function search(Request $request): array
    {
        if (empty($request->getExpression())) {
            return [];
        }

        if ($filter = !empty($resources = $request->getResources())) {
            $resources = array_map(function($value) {
                return $this->registry->find($value)->getEntityClass();
            }, $resources);
        }

        $results = [];

        foreach ($this->resources as $resource => $global) {
            if (!$global) {
                continue;
            }

            if ($filter && !in_array($resource, $resources, true)) {
                continue;
            }

            $repository = $this->getRepository($resource);

            if (!$repository->supports($request)) {
                continue;
            }

            $results = array_merge($results, $repository->search($request));
        }

        if ($request->getType() === Request::RAW) {
            // TODO
            return $results;
        }

        if ($request->getType() === Request::RESOURCE) {
            // TODO
            return $results;
        }

        usort($results, function (Result $a, Result $b) {
            if ($a->getScore() == $b->getScore()) {
                return 0;
            }

            return $a->getScore() > $b->getScore() ? -1 : 1;
        });

        return array_slice($results, 0, $request->getLimit());
    }
}

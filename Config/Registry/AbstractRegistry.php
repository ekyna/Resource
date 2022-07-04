<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\AbstractConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

use function array_keys;
use function array_replace_recursive;

/**
 * Class AbstractRegistry
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 * @implements RegistryInterface<T>
 */
abstract class AbstractRegistry implements RegistryInterface
{
    private array  $defaults;
    private array  $data;
    private array  $aliases;
    private string $configClass;
    /** @var Cache<T> */
    private Cache  $cache;

    public function __construct(array $defaults, array $data, array $aliases, string $configClass)
    {
        $this->defaults = $defaults;
        $this->data = $data;
        $this->aliases = $aliases;
        $this->configClass = $configClass;

        $this->cache = new Cache();
    }

    public function setCache(Cache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Returns whether a configuration exists for the given name.
     */
    public function has(string $name): bool
    {
        $name = $this->alias($name);

        return isset($this->data[$name]);
    }

    /**
     * Returns all the registered configurations.
     *
     * @return iterable<int, T>
     */
    public function all(): iterable
    {
        foreach (array_keys($this->data) as $name) {
            yield $name => $this->get($name);
        }
    }

    /**
     * Returns the name for the given alias.
     */
    public function alias(string $alias): string
    {
        return $this->aliases[$alias] ?? $alias;
    }

    /**
     * Returns the configuration for the given name.
     *
     * @return T
     */
    protected function get(string $name): AbstractConfig
    {
        $name = $this->alias($name);

        if (!$this->has($name)) {
            throw new NotFoundConfigurationException($name);
        }

        if ($this->cache->has($name)) {
            return $this->cache->get($name);
        }

        $data = array_replace_recursive($this->defaults, $this->data[$name]);

        $config = new $this->configClass($name, $data);

        $this->create($config);

        $this->cache->set($name, $config);

        return $config;
    }

    /**
     * Configuration create handler.
     *
     * @internal
     */
    protected function create(AbstractConfig $config): void
    {

    }
}

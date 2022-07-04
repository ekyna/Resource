<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\AbstractConfig;

/**
 * Class Cache
 * @package  Ekyna\Component\Resource\Config\Registry
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T of AbstractConfig
 */
class Cache
{
    /** @var array<T> */
    private array $configs = [];

    /**
     * Returns whether a config is cached for the given name.
     */
    public function has(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * Returns the cached config by its name.
     *
     * @return T
     */
    public function get(string $name): AbstractConfig
    {
        return $this->configs[$name];
    }

    /**
     * Sets the cache config.
     *
     * @param T $config
     */
    public function set(string $name, AbstractConfig $config): void
    {
        $this->configs[$name] = $config;
    }
}

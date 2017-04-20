<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Factory;

use Ekyna\Component\Resource\Config\Registry\AbstractRegistry;

/**
 * Class Cache
 * @package Ekyna\Component\Resource\Config\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cache
{
    /**
     * @var AbstractRegistry[]
     */
    private array $registries = [];


    /**
     * Returns whether the cache is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->registries);
    }

    /**
     * Returns whether a registry is cached for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->registries[$name]);
    }

    /**
     * Returns the cached registry by its name.
     *
     * @param string $name
     *
     * @return AbstractRegistry
     */
    public function get(string $name): AbstractRegistry
    {
        return $this->registries[$name];
    }

    /**
     * Sets the cache registry.
     *
     * @param string           $name
     * @param AbstractRegistry $registry
     */
    public function set(string $name, AbstractRegistry $registry): void
    {
        $this->registries[$name] = $registry;
    }
}

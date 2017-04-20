<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\AbstractConfig;

/**
 * Class Cache
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cache
{
    /**
     * @var AbstractConfig[]
     */
    private array $configs = [];


    /**
     * Returns whether a config is cached for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * Returns the cached config by its name.
     *
     * @param string $name
     *
     * @return AbstractConfig
     */
    public function get(string $name): AbstractConfig
    {
        return $this->configs[$name];
    }

    /**
     * Sets the cache config.
     *
     * @param string         $name
     * @param AbstractConfig $config
     */
    public function set(string $name, AbstractConfig $config)
    {
        $this->configs[$name] = $config;
    }
}

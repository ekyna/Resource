<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Factory;

use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\Registry;
use Ekyna\Component\Resource\Exception\RuntimeException;

use function is_array;
use function is_file;
use function is_readable;

/**
 * Class RegistryFactory
 * @package Ekyna\Component\Resource\Config\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class RegistryFactory implements RegistryFactoryInterface
{
    private Config $config;
    private Cache  $cache;


    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->cache = new Cache();
    }

    /**
     * Sets the cache.
     *
     * @param Cache $cache
     *
     * @return RegistryFactory
     */
    public function setCache(Cache $cache): self
    {
        if (!$this->cache->isEmpty()) {
            throw new RuntimeException('Registries have been built: it\'s too late to change the cache !');
        }

        $this->cache = $cache;

        return $this;
    }

    public function getPermissionRegistry(): Registry\PermissionRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->buildRegistry(Registry\PermissionRegistryInterface::NAME);
    }

    public function getActionRegistry(): Registry\ActionRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->buildRegistry(Registry\ActionRegistryInterface::NAME);
    }

    public function getBehaviorRegistry(): Registry\BehaviorRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->buildRegistry(Registry\BehaviorRegistryInterface::NAME);
    }

    public function getNamespaceRegistry(): Registry\NamespaceRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->buildRegistry(Registry\NamespaceRegistryInterface::NAME);
    }

    public function getResourceRegistry(): Registry\ResourceRegistryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->buildRegistry(Registry\ResourceRegistryInterface::NAME);
    }

    /**
     * Builds the registry.
     *
     * @param string $name
     *
     * @return Registry\AbstractRegistry
     */
    private function buildRegistry(string $name): Registry\AbstractRegistry
    {
        if ($this->cache->has($name)) {
            return $this->cache->get($name);
        }

        $registryClass = $this->config->getData($name, Config::REGISTRY);
        $configClass = $this->config->getData($name, Config::CONFIG);

        $defaults = $this->getDefaults($name);

        $data = $this->loadData($this->config->getData($name, Config::DATA));

        $aliases = [];
        if (null !== $filename = $this->config->getData($name, Config::ALIASES)) {
            $aliases = $this->loadData($filename);
        }

        /** @see Registry\AbstractRegistry::__construct() */
        $registry = new $registryClass($defaults, $data, $aliases, $configClass);

        $this->cache->set($name, $registry);

        return $registry;
    }

    /**
     * Loads the file data.
     *
     * @param string $filename
     *
     * @return array
     */
    private function loadData(string $filename): array
    {
        $path = $this->config->getCacheDir() . '/' . $filename;

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Registry cache file '$path' does not exists.");
        }

        $data = require $path;
        if (!is_array($data)) {
            throw new RuntimeException("Failed to read registry data from cache file '$path'.");
        }

        return $data;
    }

    /**
     * Returns the defaults for the given registry name.
     *
     * @param string $name
     *
     * @return array
     */
    private function getDefaults(string $name): array
    {
        $path = $this->config->getCacheDir() . '/defaults.php'; // TODO Use config
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Defaults cache file '$path' does not exists.");
        }

        $data = require $path;
        if (!is_array($data)) {
            throw new RuntimeException("Failed to read registry data from cache file '$path'.");
        }

        if (!isset($data[$name])) {
            throw new RuntimeException("No defaults set for registry '$name'.");
        }

        return $data[$name];
    }
}

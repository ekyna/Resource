<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Resource\Config\Builder\ConfigBuilder;
use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\Cache\PhpDumper;
use Ekyna\Component\Resource\Config\Registry;
use Ekyna\Component\Resource\Exception;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

/**
 * Class RegistryBuilder
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class RegistryBuilder
{
    private ConfigBuilder $builder;
    private Config        $config;

    private bool      $debug;
    private PhpDumper $dumper;


    /**
     * Constructor.
     *
     * @param ConfigBuilder $builder
     * @param Config        $config
     */
    public function __construct(ConfigBuilder $builder, Config $config)
    {
        // TODO use factory config for file names

        $this->builder = $builder;
        $this->config = $config;
    }

    /**
     * Builds the registries.
     *
     * @param bool $debug
     *
     * @throws Exception\ConfigurationException
     * @throws ExceptionInterface
     */
    public function build(bool $debug): void
    {
        $this->debug = $debug;

        $this->dumper = new PhpDumper();

        $this->builder->finalize();

        // Build registries
        $this->buildPermissionRegistry();
        $this->buildActionRegistry();
        $this->buildBehaviorRegistry();
        $this->buildNamespaceRegistry();
        $this->buildResourceRegistry();
        $this->buildDefaultsRegistry();
    }

    /**
     * Builds the permission registry.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildPermissionRegistry(): void
    {
        $filename = $this->config->getData(Registry\PermissionRegistryInterface::NAME, Config::DATA);
        $this->buildRegistryCache($filename, $this->builder->getPermissions());
    }

    /**
     * Builds the action registry.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildActionRegistry(): void
    {
        $filename = $this->config->getData(Registry\ActionRegistryInterface::NAME, Config::DATA);
        $this->buildRegistryCache($filename, $this->builder->getActions());

        $filename = $this->config->getData(Registry\ActionRegistryInterface::NAME, Config::ALIASES);
        $this->buildRegistryCache($filename, $this->builder->getActionsAliases());
    }

    /**
     * Builds the behavior registry.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildBehaviorRegistry(): void
    {
        $filename = $this->config->getData(Registry\BehaviorRegistryInterface::NAME, Config::DATA);
        $this->buildRegistryCache($filename, $this->builder->getBehaviors());

        $filename = $this->config->getData(Registry\BehaviorRegistryInterface::NAME, Config::ALIASES);
        $this->buildRegistryCache($filename, $this->builder->getBehaviorsAliases());
    }

    /**
     * Builds the namespace registry.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildNamespaceRegistry(): void
    {
        $filename = $this->config->getData(Registry\NamespaceRegistryInterface::NAME, Config::DATA);
        $this->buildRegistryCache($filename, $this->builder->getNamespaces());
    }

    /**
     * Builds the resource registry.
     *
     * @throws Exception\ConfigurationException
     * @throws ExceptionInterface
     */
    private function buildResourceRegistry(): void
    {
        $filename = $this->config->getData(Registry\ResourceRegistryInterface::NAME, Config::DATA);
        $this->buildRegistryCache($filename, $this->builder->getResources());

        $filename = $this->config->getData(Registry\ResourceRegistryInterface::NAME, Config::ALIASES);
        $this->buildRegistryCache($filename, $this->builder->getResourcesAliases());
    }

    /**
     * Builds the defaults registry.
     */
    private function buildDefaultsRegistry(): void
    {
        $data = [];

        foreach ($this->builder->getDefaults() as $name => $defaults) {
            $data[$name] = $defaults ? $defaults->get() : [];
        }

        $this->buildRegistryCache('defaults.php', $data);
    }

    /**
     * Builds the registry cache.
     *
     * @param string $filePath
     * @param array  $data
     */
    private function buildRegistryCache(string $filePath, array $data): void
    {
        // TODO Check before loading data ?
        $cache = new ConfigCache($this->config->getCacheDir() . '/' . $filePath, $this->debug);
        if ($cache->isFresh()) {
            return;
        }

        $content = $this->dumper->dump($data);
        $cache->write($content, $this->builder->getFiles());
    }
}

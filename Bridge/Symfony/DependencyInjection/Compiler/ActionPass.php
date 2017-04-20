<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Config\Builder\ConfigBuilder;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use function array_keys;
use function array_map;
use function class_exists;
use function is_array;
use function is_subclass_of;

/**
 * Class ActionRegistryPass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ActionPass implements CompilerPassInterface
{
    private ConfigBuilder $config;


    public function __construct(ConfigBuilder $loader)
    {
        $this->config = $loader;
    }

    public function process(ContainerBuilder $container): void
    {
        // Store actions from yaml/xml files
        $classes = array_map(function (array $config) {
            return $config['class'];
        }, $this->config->getActions());

        // Store unregistered resources actions
        $resources = $this->config->getResources();
        foreach ($resources as $resource) {
            if (!isset($resource['actions']) || !is_array($resource['actions'])) {
                continue;
            }

            foreach (array_keys($resource['actions']) as $resourceAction) {
                if (class_exists($resourceAction) && !in_array($resourceAction, $classes, true)) {
                    $classes[] = $resourceAction;
                }
            }
        }

        // Load actions services
        foreach ($container->findTaggedServiceIds(ActionInterface::DI_TAG, true) as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $definition
                // Enable auto configuration (trait setters calls)
                ->setAutoconfigured(true);

            $class = $definition->getClass();
            if (!is_subclass_of($class, ActionInterface::class)) {
                throw new RuntimeException("Class $class must implements " . ActionInterface::class);
            }

            $config = $this->config->addAction($class);

            // Change action class
            if ($class !== $config['class']) {
                $definition->setClass($class = $config['class']);
            }

            // Controllers must be public
            if ($serviceId === $class) {
                $definition->setPublic(true);
            } elseif ($container->hasAlias($class)) {
                $container->getAlias($class)->setPublic(true);
            } else {
                $container->setAlias($class, $serviceId)->setPublic(true);
            }
        }

        // Load action builders services
        foreach ($container->findTaggedServiceIds(ActionBuilderInterface::DI_TAG, true) as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            $class = $definition->getClass();
            if (!is_subclass_of($class, ActionBuilderInterface::class)) {
                throw new RuntimeException("Class $class must implements " . ActionBuilderInterface::class);
            }

            $config = $this->config->addAction($class);

            if ($class !== $config['class']) {
                $definition->setClass($config['class']);
            }
        }

        // Register actions from yaml/xml files as services
        foreach ($classes as $class) {
            // Action builder does not need to be a service
            if (is_subclass_of($class, ActionBuilderInterface::class)) {
                continue;
            }

            // Do not override configured services
            if ($container->hasDefinition($class) || $container->hasAlias($class)) {
                continue;
            }

            $definition = new Definition($class);
            $definition
                ->addTag(ActionInterface::DI_TAG)
                // Enable auto configuration (trait setters calls)
                ->setAutoconfigured(true)
                ->setAutowired(true)
                // Controllers must be public
                ->setPublic(true);

            $container->setDefinition($class, $definition);
        }
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Behavior\BehaviorBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Config\Builder\ConfigBuilder;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Persistence\PersistenceAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function array_keys;
use function array_map;
use function class_exists;
use function in_array;
use function is_array;
use function is_subclass_of;

/**
 * Class BehaviorRegistryPass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BehaviorPass implements CompilerPassInterface
{
    private ConfigBuilder $config;


    public function __construct(ConfigBuilder $loader)
    {
        $this->config = $loader;
    }

    public function process(ContainerBuilder $container): void
    {
        // Store behaviors from yaml/xml files
        $classes = array_map(function (array $config) {
            return $config['class'];
        }, $this->config->getBehaviors());

        // Store unregistered resources behaviors
        $resources = $this->config->getResources();
        foreach ($resources as $resource) {
            if (!isset($resource['behaviors']) || !is_array($resource['behaviors'])) {
                continue;
            }

            foreach (array_keys($resource['behaviors']) as $resourceBehavior) {
                if (class_exists($resourceBehavior) && !in_array($resourceBehavior, $classes, true)) {
                    $classes[] = $resourceBehavior;
                }
            }
        }

        // Loads behaviors services
        $behaviors = $aliases = [];
        foreach ($container->findTaggedServiceIds(BehaviorInterface::DI_TAG, true) as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            $class = $definition->getClass();
            if (!is_subclass_of($class, BehaviorInterface::class)) {
                throw new RuntimeException("Class $class must implements " . BehaviorInterface::class);
            }

            $config = $this->config->addBehavior($class);

            if ($class !== $config['class']) {
                $definition->setClass($config['class']);
            }

            $this->injectPersistenceHelper($definition);

            // Store reference for service locator
            $behaviors[$config['class']] = new Reference($config['class']);
            $aliases[$config['name']] = $config['class'];
        }

        // Load behavior builders services
        foreach ($container->findTaggedServiceIds(BehaviorBuilderInterface::DI_TAG, true) as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            $class = $definition->getClass();
            if (!is_subclass_of($class, BehaviorBuilderInterface::class)) {
                throw new RuntimeException("Class $class must implements " . BehaviorBuilderInterface::class);
            }

            $config = $this->config->addBehavior($class);

            if ($class !== $config['class']) {
                $definition->setClass($config['class']);
            }
        }

        // Register behaviors from yaml/xml files as services
        foreach ($classes as $class) {
            // Behavior builder does not need to be a service
            if (is_subclass_of($class, BehaviorBuilderInterface::class)) {
                continue;
            }

            if ($container->hasDefinition($class)) {
                continue;
            }

            $config = $this->config->addBehavior($class);

            $definition = new Definition($config['class']);
            $definition->addTag(BehaviorInterface::DI_TAG);
            $definition->setPublic(false);

            $this->injectPersistenceHelper($definition);

            $container->setDefinition($config['class'], $definition);

            // Store reference for service locator
            $behaviors[$config['class']] = new Reference($config['class']);
            $aliases[$config['name']] = $config['class'];
        }

        // Replace arguments with service locator and aliases map.
        $container
            ->getDefinition('ekyna_resource.behavior.registry')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $behaviors, 'resource_behaviors'))
            ->replaceArgument(1, $aliases);
    }

    private function injectPersistenceHelper(Definition $definition): void
    {
        if (!is_subclass_of($definition->getClass(), PersistenceAwareInterface::class)) {
            return;
        }

        if ($definition->hasMethodCall('setPersistenceHelper')) {
            return;
        }

        // TODO Depends on driver
        /** @see \Ekyna\Component\Resource\Doctrine\ORM\OrmExtension::DRIVER */

        $definition->addMethodCall('setPersistenceHelper', [new Reference('ekyna_resource.orm.persistence_helper')]);
    }
}

<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Component\Resource\Behavior\BehaviorExecutor;
use Ekyna\Component\Resource\Behavior\BehaviorRegistry;
use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\Factory\RegistryFactory;
use Ekyna\Component\Resource\Config\Registry;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Config registry factory
        ->set('ekyna_resource.config.registry_factory', RegistryFactory::class)
            ->args([
                inline_service(Config::class)
                    ->factory([Config::class, 'create'])
                    ->args([param('kernel.cache_dir')])
            ])
            ->alias(RegistryFactory::class, 'ekyna_resource.config.registry_factory')

        // Permission config registry
        ->set('ekyna_resource.registry.permission', Registry\PermissionRegistryInterface::class)
            ->factory([service('ekyna_resource.config.registry_factory'), 'getPermissionRegistry'])
            ->alias(Registry\PermissionRegistryInterface::class, 'ekyna_resource.registry.permission')

        // Action config registry
        ->set('ekyna_resource.registry.action', Registry\ActionRegistryInterface::class)
            ->factory([service('ekyna_resource.config.registry_factory'), 'getActionRegistry'])
            ->alias(Registry\ActionRegistryInterface::class, 'ekyna_resource.registry.action')

        // Behavior config registry
        ->set('ekyna_resource.registry.behavior', Registry\BehaviorRegistryInterface::class)
            ->factory([service('ekyna_resource.config.registry_factory'), 'getBehaviorRegistry'])
            ->alias(Registry\BehaviorRegistryInterface::class, 'ekyna_resource.registry.behavior')

        // Namespace config registry
        ->set('ekyna_resource.registry.namespace', Registry\NamespaceRegistryInterface::class)
            ->factory([service('ekyna_resource.config.registry_factory'), 'getNamespaceRegistry'])
            ->alias(Registry\NamespaceRegistryInterface::class, 'ekyna_resource.registry.namespace')

        // Resource config registry
        ->set('ekyna_resource.registry.resource', Registry\ResourceRegistryInterface::class)
            ->factory([service('ekyna_resource.config.registry_factory'), 'getResourceRegistry'])
            ->alias(Registry\ResourceRegistryInterface::class, 'ekyna_resource.registry.resource')

        // Behavior registry
        ->set('ekyna_resource.behavior.registry', BehaviorRegistry::class)
            ->args([
                abstract_arg('Behaviors service locator'), // Replaced by BehaviorPass
                abstract_arg('Behaviors aliases'),         // Replaced by BehaviorPass
            ])

        // Behavior executor
        ->set('ekyna_resource.behavior.executor', BehaviorExecutor::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.registry.behavior'),
                service('ekyna_resource.behavior.registry'),
            ])
    ;
};

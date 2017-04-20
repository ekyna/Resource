<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Extension;

use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ExtensionInterface
 * @package Ekyna\Component\Resource\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExtensionInterface
{
    public const NAME_REGEX   = '~^[a-z][a-z0-9]*(_[a-z0-9]+)*[a-z0-9]*$~';
    public const PREFIX_REGEX = '~^/[a-z][a-z0-9-][a-z0-9]*$~';

    /**
     * Extends the permission configurations.
     */
    public function extendPermissionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void;

    /**
     * Extends the namespace configurations.
     */
    public function extendNamespaceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void;

    /**
     * Extends the action configurations.
     */
    public function extendActionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void;

    /**
     * Extends the action configurations options.
     */
    public function extendActionOptions(OptionsResolver $resolver): void;

    /**
     * Extends the behavior configurations.
     */
    public function extendBehaviorConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void;

    /**
     * Extends the behavior configurations options.
     */
    public function extendBehaviorOptions(OptionsResolver $resolver): void;

    /**
     * Extends the resource configurations.
     */
    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void;

    /**
     * Configures the parameters.
     */
    public function configureParameters(DI\ContainerBuilder $container): void;

    /**
     * Configures the compiler passes.
     *
     * If you need to use the registry factory in your pass(es), the pass priority must be lower than 32.
     * Otherwise, registry factory wn't be ready.
     *
     * @see \Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler\ResourcePass::process
     * @see \Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerBuilder::configureServices
     */
    public function configurePasses(DI\ContainerBuilder $container, RegistryFactoryInterface $factory): void;

    /**
     * Configures the services.
     */
    public function configureContainer(DI\ContainerBuilder $container, RegistryFactoryInterface $factory): void;
}

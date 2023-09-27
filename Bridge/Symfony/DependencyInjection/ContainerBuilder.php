<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Behavior\BehaviorBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Bridge\Symfony\ResourceUtil;
use Ekyna\Component\Resource\Config\Builder\ConfigBuilder;
use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\Factory\RegistryFactory as WrappedRegistryFactory;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as Container;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

use function call_user_func;
use function iterator_to_array;

/**
 * Class ContainerBuilder
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ContainerBuilder
{
    private ConfigBuilder $config;

    private Config          $registryConfig;
    private RegistryFactory $registryFactory;


    /**
     * Configures the container builder.
     *
     * @param ConfigLoader $loader
     */
    public function configure(ConfigLoader $loader): void
    {
        $this->config = new ConfigBuilder($loader);
        $this->config->build();
    }

    /**
     * Builds the container.
     *
     * @param Container $container
     */
    public function build(Container $container): void
    {
        $this->registryConfig = Config::create($container->getParameter('kernel.cache_dir'));
        $this->registryFactory = new RegistryFactory(
            new WrappedRegistryFactory($this->registryConfig)
        );

        // Configures the compiler passes
        $this->configurePasses($container);

        // Configures resources parameters (resources and translations entities classes)
        // to make them available for bundles extensions.
        $this->configureParameters($container);
    }

    /**
     * Configures the container's compiler passes.
     *
     * @param Container $container
     */
    private function configurePasses(Container $container): void
    {
        // Auto tag actions
        $container
            ->registerForAutoconfiguration(ActionInterface::class)
            ->addTag(ActionInterface::DI_TAG);

        // Auto tag action builders
        $container
            ->registerForAutoconfiguration(ActionBuilderInterface::class)
            ->addTag(ActionBuilderInterface::DI_TAG);

        // Auto tag behaviors
        $container
            ->registerForAutoconfiguration(BehaviorInterface::class)
            ->addTag(BehaviorInterface::DI_TAG);

        // Auto tag behavior builders
        $container
            ->registerForAutoconfiguration(BehaviorBuilderInterface::class)
            ->addTag(BehaviorBuilderInterface::DI_TAG);

        // TODO auto tag resource event listeners and subscribers

        // TODO Read resources services by tag (factory, repository and manger)
        // for auto configuration (loader, resource config entries)

        // Registers and prepend configurations of actions and action builders.
        $container->addCompilerPass(
            new Compiler\ActionPass($this->config),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            64
        );

        // Registers and prepend configurations of behaviors and behavior builders.
        $container->addCompilerPass(
            new Compiler\BehaviorPass($this->config),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            64
        );

        // Build and configures resources services
        $container->addCompilerPass(
            new Compiler\ResourcePass($this),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            32
        );

        // Register resource factories services
        $container->addCompilerPass(
            new Compiler\ResourceServicesPass(ResourceFactoryInterface::DI_TAG, 'ekyna_resource.factory'),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            16
        );

        // Register resource repositories services
        $container->addCompilerPass(
            new Compiler\ResourceServicesPass(ResourceRepositoryInterface::DI_TAG, 'ekyna_resource.repository'),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            16
        );

        // Register resource managers services
        $container->addCompilerPass(
            new Compiler\ResourceServicesPass(ResourceManagerInterface::DI_TAG, 'ekyna_resource.manager'),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            16
        );

        // Registers resource events listeners and subscribers
        $container->addCompilerPass(new RegisterListenersPass(
            'ekyna_resource.event_dispatcher',
            'resource.event_listener',
            'resource.event_subscriber'
        ));

        // Add compiler passes through extensions
        foreach ($this->config->getExtensions() as $extension) {
            $extension->configurePasses($container, $this->registryFactory);
        }
    }

    /**
     * Builds the resource's container parameters.
     *
     * @param Container $container
     */
    private function configureParameters(Container $container): void
    {
        foreach ($this->config->getResources() as $resource) {
            $name = ResourceUtil::getClassParameter($resource['namespace'], $resource['name']);

            if (!$container->hasParameter($name)) {
                $container->setParameter($name, $resource['entity']['class']);
            }

            if (empty($resource['translation'])) {
                continue;
            }

            $name = ResourceUtil::getTranslationClassParameter($resource['namespace'], $resource['name']);

            if (!$container->hasParameter($name)) {
                $container->setParameter($name, $resource['translation']['class']);
            }
        }

        // Build parameters through extensions
        foreach ($this->config->getExtensions() as $extension) {
            $extension->configureParameters($container);
        }
    }

    /**
     * Builds the resource's container services.
     *
     * @param Container $container
     *
     * @throws ConfigurationException
     */
    public function configureServices(Container $container): void
    {
        $this->buildRegistries($container, $this->registryConfig);
        $this->registryFactory->setReady();

        /** @var ResourceConfig[] $resources */
        $resources = iterator_to_array($this->registryFactory->getResourceRegistry()->all());

        foreach ($resources as $resource) {
            // Configures the metadata service
            $this->configureMetadata($container, $resource);

            // Configure behaviors
            $this->configureBehaviors($container, $resource);
        }

        // Build container through extensions
        foreach ($this->config->getExtensions() as $extension) {
            $extension->configureContainer($container, $this->registryFactory);
        }
    }

    /**
     * Builds the config registries.
     *
     * @throws ConfigurationException
     */
    private function buildRegistries(Container $container, Config $config): void
    {
        $builder = new RegistryBuilder($this->config, $config);

        $builder->build($container->getParameter('kernel.debug'));
    }

    /**
     * Configures the resource metadata.
     *
     * @TODO Move into ORmExtension
     */
    private function configureMetadata(Container $container, ResourceConfig $resource): void
    {
        $serviceId = ResourceUtil::getServiceId('metadata', $resource);

        if ($container->has($serviceId)) {
            return;
        }

        $definition = new DI\Definition(ClassMetadata::class);
        $definition
            ->setFactory([new DI\Reference('doctrine.orm.default_entity_manager'), 'getClassMetadata'])
            ->setArguments([$resource->getEntityClass()]);

        $container->setDefinition($serviceId, $definition);
    }

    /**
     * Configures the resource behaviors.
     *
     * @param Container      $container
     * @param ResourceConfig $resource
     */
    private function configureBehaviors(Container $container, ResourceConfig $resource): void
    {
        $behaviors = $this->config->getBehaviors();
        $behaviorAliases = $this->config->getBehaviorsAliases();

        foreach ($resource->getBehaviors() as $behavior => $options) {
            if (isset($behaviorAliases[$behavior])) {
                $behavior = $behaviorAliases[$behavior];
            }

            if (!isset($behaviors[$behavior])) {
                throw new RuntimeException("Unknown behavior '$behavior'.");
            }

            /** @see BehaviorInterface::buildContainer */
            call_user_func([$behaviors[$behavior]['class'], 'buildContainer'], $container, $resource, $options);
        }
    }
}

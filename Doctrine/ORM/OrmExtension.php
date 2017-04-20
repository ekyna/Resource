<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerHelper;
use Ekyna\Component\Resource\Bridge\Symfony\ResourceUtil;
use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Cache\ResultCacheAwareInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\TranslatableFactory;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Factory\TranslatableFactoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder as Container;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_array;
use function is_subclass_of;
use function iterator_to_array;

/**
 * Class OrmExtension
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * TODO Use this extension (need sf 4.3+)
 * @see     \Ekyna\Component\Resource\Extension\CoreExtension::extendResourceConfig
 */
class OrmExtension extends AbstractExtension
{
    public const DRIVER = 'doctrine/orm';

    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $d = [
            'driver' => self::DRIVER,
        ];

        $defaults->add($d);

        $resolver->setDefaults($d);

        $resolver
            ->addNormalizer('repository', function (Options $options, $value) {
                if ($options['driver'] !== self::DRIVER) {
                    return $value;
                }

                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    $interface = TranslatableRepositoryInterface::class;
                    $class = Repository\TranslatableRepository::class;
                } else {
                    $interface = ResourceRepositoryInterface::class;
                    $class = Repository\ResourceRepository::class;
                }

                return $this->normalizeClassInterface($value, $interface, $class);
            })
            ->addNormalizer('manager',
                function (Options $options, $value) {
                    if ($options['driver'] !== self::DRIVER) {
                        return $value;
                    }

                    return $this->normalizeClassInterface(
                        $value,
                        ResourceManagerInterface::class,
                        Manager\ResourceManager::class
                    );
                }
            )
            ->addNormalizer('factory', function (Options $options, $value) {
                if ($options['driver'] !== self::DRIVER) {
                    return $value;
                }

                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    $interface = TranslatableFactoryInterface::class;
                    $class = Factory\TranslatableFactory::class;
                } else {
                    $interface = ResourceFactoryInterface::class;
                    $class = Factory\ResourceFactory::class;
                }

                return $this->normalizeClassInterface($value, $interface, $class);
            });
    }

    public function configureContainer(Container $container, RegistryFactoryInterface $factory): void
    {
        $container
            ->getDefinition('ekyna_resource.factory.factory')
            ->addMethodCall('registerAdapter', [
                new DI\Reference('ekyna_resource.factory.factory.orm_adapter'),
            ]);

        $container
            ->getDefinition('ekyna_resource.repository.factory')
            ->addMethodCall('registerAdapter', [
                new DI\Reference('ekyna_resource.repository.factory.orm_adapter'),
            ]);

        $container
            ->getDefinition('ekyna_resource.manager.factory')
            ->addMethodCall('registerAdapter', [
                new DI\Reference('ekyna_resource.manager.factory.orm_adapter'),
            ]);

        // Classes maps
        // Previously it was the following parameters :
        // - ekyna_resource.entities
        // - ekyna_resource.interfaces
        // - ekyna_resource.translation_mapping
        $classes = $interfaces = $translations = [];

        /** @var ResourceConfig[] $resources */
        $resources = iterator_to_array($factory->getResourceRegistry()->all());

        foreach ($resources as $resource) {
            $this->configureFactory($container, $resource);
            $this->configureRepository($container, $resource);
            $this->configureManager($container, $resource);

            // Add entity class
            $classes[] = $entity = $resource->getEntityClass();

            // Add translation classes to map
            if (!empty($translation = $resource->getTranslationClass())) {
                $translations[$translation] = $entity;
                $translations[$entity] = $classes[] = $translation;

                // Add translation interface to map
                if ($interface = $resource->getTranslationInterface()) {
                    $interfaces[$interface] = $translation;
                }
            }

            // Add entity interface to map
            if ($interface = $resource->getEntityInterface()) {
                $interfaces[$interface] = $entity;
            }
        }

        $container
            ->getDefinition('ekyna_resource.orm.metadata_listener')
            ->replaceArgument(1, $classes);

        $container
            ->getDefinition('ekyna_resource.orm.translatable_listener')
            ->replaceArgument(2, $translations);

        $definition = $container->getDefinition('doctrine.orm.listeners.resolve_target_entity');

        foreach ($interfaces as $interface => $entity) {
            $definition->addMethodCall('addResolveTargetEntity', [$interface, $entity, []]);
        }

        $definition
            ->clearTag('doctrine.event_subscriber')
            ->addTag('doctrine.event_subscriber', ['priority' => -512]); // After *subject metadata subscribers
    }

    private function configureFactory(Container $container, ResourceConfig $config): void
    {
        $id = ResourceUtil::getServiceId('factory', $config);

        $configureFound = function (DI\Definition $definition) use ($container, $config, $id) {
            // Add factory service tag
            $definition->addTag(ResourceFactoryInterface::DI_TAG, [
                'resource' => $config->getEntityClass(),
            ]);

            // Set class
            if (!$definition->hasMethodCall('setClass')) {
                $definition->addMethodCall('setClass', [
                    $config->getEntityClass(),
                ]);
            }

            // Inject manager registry
            if (!$definition->hasMethodCall('setManagerRegistry')) {
                $definition->addMethodCall('setManagerRegistry', [
                    new DI\Reference('ekyna_resource.orm.manager_registry'),
                ]);
            }

            // Inject locale provider if needed
            if (
                is_subclass_of($config->getRepositoryClass(), LocaleProviderAwareInterface::class, true)
                && !$definition->hasMethodCall('setLocaleProvider')
            ) {
                $definition->addMethodCall('setLocaleProvider', [
                    new DI\Reference('ekyna_resource.provider.locale'),
                ]);
            }
        };

        $create = function () use ($container, $config, $id): DI\Definition {
            $definition = new DI\Definition($config->getRepositoryClass());
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.factory.factory'), 'getFactory'])
                ->addArgument($config->getEntityClass())
                ->setPublic(true);

            return $definition;
        };

        if (is_subclass_of($config->getEntityClass(), TranslatableInterface::class, true)) {
            $class = TranslatableFactory::class;
            $interface = TranslatableFactoryInterface::class;
        } else {
            $class = ResourceFactory::class;
            $interface = ResourceFactoryInterface::class;
        }

        // Factories must be public in dev/test environment for fixtures.
        $configure = null;
        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $configure = function (DI\Definition $definition): void {
                $definition->setPublic(true);
            };
        }

        ContainerHelper::configureService($container, [
            'id'                => $id,
            'class'             => $config->getFactoryClass(),
            'interface'         => $config->getFactoryInterface(),
            'default_class'     => $class,
            'default_interface' => $interface,
            'configure_found'   => $configureFound,
            'create'            => $create,
            'configure'         => $configure,
        ]);
    }

    private function configureRepository(Container $container, ResourceConfig $config): void
    {
        $id = ResourceUtil::getServiceId('repository', $config);

        $configureFound = function (DI\Definition $definition) use ($container, $config, $id) {
            // Add repository service tag
            $definition->addTag(ResourceRepositoryInterface::DI_TAG, [
                'resource' => $config->getEntityClass(),
            ]);

            // Inject result cache if needed
            if (
                is_subclass_of($config->getRepositoryClass(), ResultCacheAwareInterface::class, true)
                && !$definition->hasMethodCall('setResultCache')
            ) {
                $definition->addMethodCall('setResultCache', [
                    new DI\Reference('doctrine.orm.default_result_cache'),
                ]);
            }

            // Inject locale provider if needed
            if (
                is_subclass_of($config->getRepositoryClass(), LocaleProviderAwareInterface::class, true)
                && !$definition->hasMethodCall('setLocaleProvider')
            ) {
                $definition->addMethodCall('setLocaleProvider', [
                    new DI\Reference('ekyna_resource.provider.locale'),
                ]);
            }

            // Translatable repository configuration
            if (
                is_subclass_of($config->getRepositoryClass(), TranslatableRepositoryInterface::class, true)
                && !$definition->hasMethodCall('setTranslatableFields')
            ) {
                $definition->addMethodCall('setTranslatableFields', [$config->getTranslationFields()]);
            }

            // Inject wrapped repository if needed
            if (!$definition->hasMethodCall('setWrapped')) {
                $wrapped = new DI\Definition(EntityRepository::class);
                $wrapped
                    ->setFactory([new DI\Reference('doctrine'), 'getRepository'])
                    ->addArgument($config->getEntityClass());

                $definition->addMethodCall('setWrapped', [$wrapped]);
            }
        };

        $create = function () use ($container, $config, $id): DI\Definition {
            $definition = new DI\Definition($config->getRepositoryClass());
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.repository.factory'), 'getRepository'])
                ->addArgument($config->getEntityClass());

            return $definition;
        };

        if (is_subclass_of($config->getEntityClass(), TranslatableInterface::class, true)) {
            $class = TranslatableRepository::class;
            $interface = TranslatableRepositoryInterface::class;
        } else {
            $class = ResourceRepository::class;
            $interface = ResourceRepositoryInterface::class;
        }

        ContainerHelper::configureService($container, [
            'id'                => $id,
            'class'             => $config->getRepositoryClass(),
            'interface'         => $config->getRepositoryInterface(),
            'default_class'     => $class,
            'default_interface' => $interface,
            'configure_found'   => $configureFound,
            'create'            => $create,
        ]);
    }

    private function configureManager(Container $container, ResourceConfig $config): void
    {
        $id = ResourceUtil::getServiceId('manager', $config);

        $configureFound = function (DI\Definition $definition) use ($container, $config, $id) {
            // Add manager service tag
            $definition->addTag(ResourceManagerInterface::DI_TAG, [
                'resource' => $config->getEntityClass(),
            ]);

            // Configure if needed
            if (!$definition->hasMethodCall('configure')) {
                $definition->addMethodCall('configure', [
                    $config->getEntityClass(),
                    $config->getId(),
                    new DI\Parameter('kernel.debug'),
                ]);
            }

            // Inject event dispatcher if needed
            if (!$definition->hasMethodCall('setWrapped')) {
                $definition->addMethodCall('setDispatcher', [
                    new DI\Reference('ekyna_resource.event_dispatcher'),
                ]);
            }

            // Inject wrapped manager if needed
            if (!$definition->hasMethodCall('setWrapped')) {
                $wrapped = new DI\Definition(EntityManagerInterface::class);
                $wrapped
                    ->setFactory([new DI\Reference('doctrine'), 'getManagerForClass'])
                    ->addArgument($config->getEntityClass());

                $definition->addMethodCall('setWrapped', [$wrapped]);
            }
        };

        $create = function () use ($container, $config, $id): DI\Definition {
            $definition = new DI\Definition($config->getRepositoryClass());
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.manager.factory'), 'getManager'])
                ->addArgument($config->getEntityClass());

            return $definition;
        };

        ContainerHelper::configureService($container, [
            'id'                => $id,
            'class'             => $config->getRepositoryClass(),
            'interface'         => $config->getRepositoryInterface(),
            'default_class'     => ResourceManager::class,
            'default_interface' => ResourceManagerInterface::class,
            'configure_found'   => $configureFound,
            'create'            => $create,
        ]);
    }
}

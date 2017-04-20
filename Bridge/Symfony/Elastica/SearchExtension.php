<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Elastica;

use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerHelper;
use Ekyna\Component\Resource\Bridge\Symfony\ResourceUtil;
use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;
use Ekyna\Component\Resource\Search\SearchRepositoryInterface;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder as Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function is_bool;
use function is_null;
use function iterator_to_array;
use function sprintf;

/**
 * Class SearchExtension
 * @package Ekyna\Component\Resource\Bridge\Symfony\Elastica
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SearchExtension extends AbstractExtension
{
    private array $resources;
    private array $repositories;
    private array $indexes;
    private array $transformers;
    private array $config;

    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = ['search' => false];

        $defaults->add($options);

        $resolver
            ->setDefaults($options)
            ->setAllowedTypes('search', ['null', 'bool', 'string', 'array'])
            ->setAllowedValues('search', function ($value) {
                if (is_bool($value)) {
                    return true;
                }

                return $this->validateClassInterface($value, SearchRepositoryInterface::class, false);
            })
            ->setNormalizer('search',
                function (Options $options, $value) {
                    if (false === $value) {
                        return [
                            'interface' => null,
                            'class'     => null,
                            'global'    => false,
                        ];
                    } elseif (is_bool($value) || is_null($value)) {
                        return [
                            'interface' => SearchRepositoryInterface::class,
                            'class'     => SearchRepository::class,
                            'global'    => false,
                        ];
                    }

                    $value = $this->normalizeClassInterface(
                        $value,
                        SearchRepositoryInterface::class,
                        SearchRepository::class
                    );

                    return array_replace(['global' => false], $value);
                }
            );
    }

    public function configureContainer(Container $container, RegistryFactoryInterface $factory): void
    {
        $this->repositories = [];
        $this->indexes = [];
        $this->transformers = [];
        $this->config = [];

        /** @var ResourceConfig[] $resources */
        $resources = iterator_to_array($factory->getResourceRegistry()->all());

        foreach ($resources as $resource) {
            $this->configureRepository($container, $resource);
        }

        $container
            ->getDefinition('ekyna_resource.factory.search_repository')
            ->replaceArgument(1, ServiceLocatorTagPass::register($container, $this->repositories))
            ->replaceArgument(2, ServiceLocatorTagPass::register($container, $this->indexes))
            ->replaceArgument(3, ServiceLocatorTagPass::register($container, $this->transformers))
            ->replaceArgument(4, $this->config);

        $container
            ->getDefinition('ekyna_resource.search')
            ->replaceArgument(2, $this->resources);
    }

    /**
     * Configures the resource search services.
     */
    private function configureRepository(Container $container, ResourceConfig $resource): void
    {
        if (empty($search = $resource->getData('search'))) {
            return;
        }

        if (is_null($class = $search['class'])) {
            return;
        }

        $this->resources[$resource->getEntityClass()] = $search['global'];

        $serviceId = ResourceUtil::getServiceId('search', $resource);

        $configureFound = function (DI\Definition $definition) use ($container, $resource, $serviceId): void {
            // Inject searchable (index)
            if (!$definition->hasMethodCall('setSearchable')) {
                $definition->addMethodCall('setSearchable', [
                    new Reference(sprintf('fos_elastica.index.%s', $resource->getId())),
                ]);
            }

            // Inject transformer
            if (!$definition->hasMethodCall('setTransformer')) {
                $definition->addMethodCall('setTransformer', [
                    new Reference(sprintf('fos_elastica.elastica_to_model_transformer.%s', $resource->getId())),
                ]);
            }

            // Inject locale provider if needed
            if (is_subclass_of($definition->getClass(), LocaleProviderAwareInterface::class, true)) {
                if (!$definition->hasMethodCall('setLocaleProvider')) {
                    $definition->addMethodCall('setLocaleProvider', [
                        new DI\Reference('ekyna_resource.provider.locale'),
                    ]);
                }
            }

            $this->repositories[$resource->getEntityClass()] = new Reference($serviceId);
        };

        $create = function () use ($class, $resource, $container): DI\Definition {
            $definition = new DI\Definition($class);
            $definition
                ->setFactory([new DI\Reference('ekyna_resource.factory.search_repository'), 'getRepository'])
                ->addArgument($resource->getEntityClass())
                ->setPublic(false);

            $id = $resource->getId();
            $indexId = sprintf('fos_elastica.index.%s', $id);
            if (!$container->hasDefinition($indexId)) {
                throw new LogicException(sprintf('Elastica index not found for resource \'%s\'.', $id));
            }

            $transformerId = sprintf('fos_elastica.elastica_to_model_transformer.%s', $id);
            if (!$container->hasDefinition($indexId)) {
                throw new LogicException(sprintf('Elastica transformer not found for resource \'%s\'.', $id));
            }

            $resourceClass = $resource->getEntityClass();
            $this->indexes[$resourceClass] = new Reference($indexId);
            $this->transformers[$resourceClass] = new Reference($transformerId);
            $this->config[$resourceClass] = $class;

            return $definition;
        };

        ContainerHelper::configureService($container, [
            'id'                => $serviceId,
            'class'             => $class,
            'interface'         => $search['interface'] ?? null,
            'default_class'     => SearchRepository::class,
            'default_interface' => SearchRepositoryInterface::class,
            'configure_found'   => $configureFound,
            'create'            => $create,
        ]);
    }
}

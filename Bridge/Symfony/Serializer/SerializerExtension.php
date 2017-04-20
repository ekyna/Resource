<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Serializer;

use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerHelper;
use Ekyna\Component\Resource\Bridge\Symfony\ResourceUtil;
use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder as Container;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function class_exists;
use function is_array;
use function is_bool;
use function is_null;
use function is_string;
use function is_subclass_of;

/**
 * Class SerializerExtension
 * @package Ekyna\Component\Resource\Bridge\Symfony\Serializer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SerializerExtension extends AbstractExtension
{
    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'normalizer' => false,
        ];

        $defaults->add($options);

        $resolver->setDefaults($options);

        $resolver
            ->setAllowedTypes('normalizer', ['null', 'bool', 'string'])
            ->setAllowedValues('normalizer', function ($value) {
                if (is_null($value) || is_bool($value)) {
                    return true;
                }

                $this->validateNormalizerClass($value);

                return true;
            })
            ->setNormalizer('normalizer', function (Options $options, $value) {
                if (false === $value) {
                    return null;
                }

                if (is_string($value)) {
                    return $value;
                }

                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    return TranslatableNormalizer::class;
                }

                return ResourceNormalizer::class;
            });
            /* TODO $resolver
            ->addNormalizer('translation', function (Options $options, $value) {
                if (!$options['normalizer']) {
                    return;
                }

                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    if (isset($value['normalizer'])) {
                        $this->validateNormalizerClass($value['normalizer']);
                    } else {
                        $value['normalizer'] = TranslationNormalizer::class;
                    }
                }

                return $value;
            });*/
    }

    public function configureContainer(Container $container, RegistryFactoryInterface $factory): void
    {
        foreach ($factory->getResourceRegistry()->all() as $resource) {
            $this->configureNormalizer($container, $resource);
        }
    }

    /**
     * Validates the normalizer class.
     */
    private function validateNormalizerClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidOptionsException("Class $class does not exist");
        }

        if (!is_subclass_of($class, NormalizerInterface::class, true)) {
            throw new InvalidOptionsException("Class $class must implement " . NormalizerInterface::class);
        }

        if (!is_subclass_of($class, DenormalizerInterface::class, true)) {
            throw new InvalidOptionsException("Class $class must implement " . DenormalizerInterface::class);
        }
    }

    /**
     * Configures the resource normalizer.
     */
    private function configureNormalizer(Container $container, ResourceConfig $resource): void
    {
        if (empty($class = $resource->getData('normalizer'))) {
            return;
        }

        $definition = ContainerHelper::configureService($container, [
            'id'            => ResourceUtil::getServiceId('normalizer', $resource),
            'class'         => $class,
            'default_class' => ResourceNormalizer::class,
        ]);
        $this->addTagsAndCalls($definition, ResourceUtil::getResourceClassParameter($resource));

        // --> Translation normalizer

        if (!$translation = $resource->getData('translation')) {
            return;
        }

        if (!isset($translation['normalizer'])) {
            return;
        }

        $definition = ContainerHelper::configureService($container, [
            'id'            => ResourceUtil::getServiceId('normalizer', $resource, true),
            'class'         => $translation['normalizer'],
            'default_class' => TranslationNormalizer::class,
        ]);
        $this->addTagsAndCalls(
            $definition,
            ResourceUtil::getResourceTranslationClassParameter($resource)
        );

        // Inject translation fields
        if (!$definition->hasMethodCall('setTranslationFields')) {
            $definition->addMethodCall('setTranslationFields', [$resource->getTranslationFields()]);
        }
    }

    /**
     * Configures the normalizer definition.
     */
    private function addTagsAndCalls(DI\Definition $definition, string $class): void
    {
        // Tags
        if (!$definition->hasTag('serializer.normalizer')) {
            $definition->addTag('serializer.normalizer');
        }
        if (!$definition->hasTag('serializer.denormalizer')) {
            $definition->addTag('serializer.denormalizer');
        }

        // Inject data class
        if (!$definition->hasMethodCall('setClass')) {
            $definition->addMethodCall('setClass', [
                new DI\Parameter($class),
            ]);
        }

        // Inject name converter
        if (!$definition->hasMethodCall('setNameConverter')) {
            $definition->addMethodCall('setNameConverter', [
                new DI\Reference('serializer.name_converter.camel_case_to_snake_case'),
            ]);
        }

        // Inject property accessor
        if (!$definition->hasMethodCall('setPropertyAccessor')) {
            $definition->addMethodCall('setPropertyAccessor', [
                new DI\Reference('serializer.property_accessor'),
            ]);
        }
    }
}

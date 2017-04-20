<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface BehaviorInterface
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BehaviorInterface
{
    public const DI_TAG = 'ekyna_resource.behavior';


    /**
     * Insert event handler.
     *
     * @param ResourceInterface $resource
     * @param array             $options
     */
    public function onInsert(ResourceInterface $resource, array $options): void;

    /**
     * Update event handler.
     *
     * @param ResourceInterface $resource
     * @param array             $options
     */
    public function onUpdate(ResourceInterface $resource, array $options): void;

    /**
     * Delete event handler.
     *
     * @param ResourceInterface $resource
     * @param array             $options
     */
    public function onDelete(ResourceInterface $resource, array $options): void;

    /**
     * Load event handler.
     *
     * @param ResourceInterface $resource
     * @param array             $options
     */
    public function onLoad(ResourceInterface $resource, array $options): void;

    /**
     * Load metadata event handler.
     *
     * @param ClassMetadataInfo $metadata
     * @param array             $options
     */
    public function onMetadata(ClassMetadataInfo $metadata, array $options): void;

    /**
     * Configures the behavior.
     * Keys 'name', 'label', 'interface' and 'operations' must be defined and non empty.
     *
     * @return array
     */
    public static function configureBehavior(): array;

    /**
     * Configures options that must be defined on resources.
     *
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver): void;

    /**
     * Builds the actions.
     *
     * @param array $actions  The resource actions configurations
     * @param array $resource The resource configuration
     * @param array $options  The behavior options
     *
     * @return array The built actions.
     */
    public static function buildActions(array $actions, array $resource, array $options): array;

    /**
     * Builds the behavior services.
     *
     * @param ContainerBuilder $container The services container builder
     * @param ResourceConfig   $resource  The resource configuration
     * @param array            $options   The behavior options
     */
    public static function buildContainer(ContainerBuilder $container, ResourceConfig $resource, array $options): void;
}

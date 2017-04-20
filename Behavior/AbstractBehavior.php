<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractBehavior
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractBehavior implements BehaviorInterface
{
    /**
     * @inheritDoc
     */
    public function onInsert(ResourceInterface $resource, array $options): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceInterface $resource, array $options): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onDelete(ResourceInterface $resource, array $options): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onLoad(ResourceInterface $resource, array $options): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onMetadata(ClassMetadataInfo $metadata, array $options): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function buildActions(array $actions, array $resource, array $options): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function buildContainer(ContainerBuilder $container, ResourceConfig $resource, array $options): void
    {

    }
}

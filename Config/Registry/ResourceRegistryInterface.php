<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface ResourceRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<ResourceConfig>
 */
interface ResourceRegistryInterface extends RegistryInterface
{
    public const NAME = 'resource';

    /**
     * Finds the resource configuration for the given resource (object/class/name)
     *
     * @param ResourceInterface|string $resource object, class or name.
     */
    public function find(ResourceInterface|string $resource, bool $throwException = true): ?ResourceConfig;

    /**
     * Finds a configuration for the given translation (object/class)
     *
     * @param TranslationInterface|string $translation object or class.
     */
    public function findByTranslation(TranslationInterface|string $translation, bool $throwException = true): ?ResourceConfig;

    /**
     * Returns the hierarchy map.
     *
     * @return array
     */
    public function getParentMap(): array;

    /**
     * Returns the depth map.
     *
     * @return array
     */
    public function getDepthMap(): array;

    /**
     * Returns the event priority map.
     *
     * @return array
     */
    public function getEventPriorityMap(): array;
}

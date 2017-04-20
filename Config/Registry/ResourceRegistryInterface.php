<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Closure;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Generator;

/**
 * Interface ResourceRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceRegistryInterface
{
    public const NAME = 'resource';

    /**
     * Returns whether a configuration is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns all the registered configurations.
     *
     * @return Generator|ResourceConfig[]
     */
    public function all(): Generator;

    /**
     * Finds the resource configuration for the given resource (object/class/name)
     *
     * @param ResourceInterface|string $resource object, class or name.
     * @param bool                     $throwException
     *
     * @return ResourceConfig|null
     */
    public function find($resource, bool $throwException = true): ?ResourceConfig;

    /**
     * Finds a configuration for the given translation (object/class)
     *
     * @param TranslationInterface|string $translation object or class.
     * @param bool                        $throwException
     *
     * @return ResourceConfig|null
     */
    public function findByTranslation($translation, bool $throwException = true): ?ResourceConfig;

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

    /**
     * Returns the name for the given alias.
     */
    public function alias(string $alias): string;
}

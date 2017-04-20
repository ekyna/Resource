<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\BehaviorConfig;
use Generator;

/**
 * Interface BehaviorRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BehaviorRegistryInterface
{
    public const NAME = 'behavior';

    /**
     * Returns whether or not a configuration is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns all the registered configurations.
     *
     * @return Generator|BehaviorConfig[]
     */
    public function all(): Generator;

    /**
     * Finds the behavior configuration by name or class.
     *
     * @param string $behavior
     * @param bool   $throwException
     *
     * @return BehaviorConfig|null
     */
    public function find(string $behavior, bool $throwException = true): ?BehaviorConfig;

    /**
     * Returns the name for the given alias.
     */
    public function alias(string $alias): string;
}

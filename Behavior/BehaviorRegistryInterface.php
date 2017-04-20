<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

/**
 * Interface BehaviorRegistryInterface
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BehaviorRegistryInterface
{
    /**
     * Returns whether or not a resource behavior is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasBehavior(string $name): bool;

    /**
     * Returns the resource behavior for the given name.
     *
     * @param string $name
     *
     * @return BehaviorInterface
     */
    public function getBehavior(string $name): BehaviorInterface;
}

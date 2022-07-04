<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

/**
 * Interface RegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @template C
 */
interface RegistryInterface
{
    /**
     * Returns whether a configuration exists for the given name.
     */
    public function has(string $name): bool;

    /**
     * Returns all the registered configurations.
     *
     * @return iterable<int, C>
     */
    public function all(): iterable;

    /**
     * Returns the name for the given alias.
     */
    public function alias(string $alias): string;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\NamespaceConfig;
use Generator;

/**
 * Interface NamespaceRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NamespaceRegistryInterface
{
    public const NAME = 'namespace';

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
     * @return Generator|NamespaceConfig[]
     */
    public function all(): Generator;

    /**
     * Finds the namespace configuration by its name.
     *
     * @param string $namespace
     * @param bool   $throwException
     *
     * @return NamespaceConfig|null
     */
    public function find(string $namespace, bool $throwException = true): ?NamespaceConfig;
}

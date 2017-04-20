<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\PermissionConfig;
use Generator;

/**
 * Interface PermissionRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PermissionRegistryInterface
{
    public const NAME = 'permission';

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
     * @return Generator|PermissionConfig[]
     */
    public function all(): Generator;

    /**
     * Finds the permission configuration by its name.
     *
     * @param string $permission
     * @param bool   $throwException
     *
     * @return PermissionConfig|null
     */
    public function find(string $permission, bool $throwException = true): ?PermissionConfig;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\PermissionConfig;

/**
 * Interface PermissionRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<PermissionConfig>
 */
interface PermissionRegistryInterface extends RegistryInterface
{
    public const NAME = 'permission';

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

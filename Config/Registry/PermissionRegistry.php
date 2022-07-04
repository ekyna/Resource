<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\PermissionConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

/**
 * Class PermissionRegistry
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<PermissionConfig>
 */
class PermissionRegistry extends AbstractRegistry implements PermissionRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $permission, bool $throwException = true): ?PermissionConfig
    {
        if ($this->has($permission)) {
            return $this->get($permission);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($permission);
        }

        return null;
    }
}

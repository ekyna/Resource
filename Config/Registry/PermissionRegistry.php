<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\PermissionConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Generator;

/**
 * Class PermissionRegistry
 * @package      Ekyna\Component\Resource\Config\Registry
 * @author       Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Generator|PermissionConfig[] all()
 * @noinspection PhpSuperClassIncompatibleWithInterfaceInspection
 */
class PermissionRegistry extends AbstractRegistry implements PermissionRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $permission, bool $throwException = true): ?PermissionConfig
    {
        if ($this->has($permission)) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->get($permission);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($permission);
        }

        return null;
    }
}

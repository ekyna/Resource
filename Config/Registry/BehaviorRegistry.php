<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\BehaviorConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

/**
 * Class BehaviorRegistry
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<BehaviorConfig>
 */
class BehaviorRegistry extends AbstractRegistry implements BehaviorRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $behavior, bool $throwException = true): ?BehaviorConfig
    {
        if ($this->has($behavior)) {
            return $this->get($behavior);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($behavior);
        }

        return null;
    }
}

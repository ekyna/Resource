<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\NamespaceConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

/**
 * Class NamespaceRegistry
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<NamespaceConfig>
 */
class NamespaceRegistry extends AbstractRegistry implements NamespaceRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $namespace, bool $throwException = true): ?NamespaceConfig
    {
        if ($this->has($namespace)) {
            return $this->get($namespace);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($namespace);
        }

        return null;
    }
}

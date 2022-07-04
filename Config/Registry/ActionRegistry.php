<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

/**
 * Class ActionRegistry
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<ActionConfig>
 */
class ActionRegistry extends AbstractRegistry implements ActionRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $action, bool $throwException = true): ?ActionConfig
    {
        if ($this->has($action)) {
            return $this->get($action);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($action);
        }

        return null;
    }
}

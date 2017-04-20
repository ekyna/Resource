<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Generator;

/**
 * Class ActionRegistry
 * @package      Ekyna\Component\Resource\Config\Registry
 * @author       Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Generator|ActionConfig[] all() Returns all the action configurations.
 * @noinspection PhpSuperClassIncompatibleWithInterfaceInspection
 */
class ActionRegistry extends AbstractRegistry implements ActionRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $action, bool $throwException = true): ?ActionConfig
    {
        if ($this->has($action)) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->get($action);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($action);
        }

        return null;
    }
}

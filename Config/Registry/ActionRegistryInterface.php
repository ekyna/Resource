<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\ActionConfig;

/**
 * Interface ActionRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<ActionConfig>
 */
interface ActionRegistryInterface extends RegistryInterface
{
    public const NAME = 'action';

    /**
     * Finds the action configuration by its name or class.
     *
     * @param string $action
     * @param bool   $throwException
     *
     * @return ActionConfig|null
     */
    public function find(string $action, bool $throwException = true): ?ActionConfig;
}

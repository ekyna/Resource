<?php
/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\ActionConfig;
use Generator;

/**
 * Interface ActionRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ActionRegistryInterface
{
    public const NAME = 'action';

    /**
     * Returns whether a configuration is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns all the registered configurations.
     *
     * @return Generator|ActionConfig[]
     */
    public function all(): Generator;

    /**
     * Finds the action configuration by its name or class.
     *
     * @param string $action
     * @param bool   $throwException
     *
     * @return ActionConfig|null
     */
    public function find(string $action, bool $throwException = true): ?ActionConfig;

    /**
     * Returns the name for the given alias.
     */
    public function alias(string $alias): string;
}

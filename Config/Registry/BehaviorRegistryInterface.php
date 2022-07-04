<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Ekyna\Component\Resource\Config\BehaviorConfig;

/**
 * Interface BehaviorRegistryInterface
 * @package Ekyna\Component\Resource\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RegistryInterface<BehaviorConfig>
 */
interface BehaviorRegistryInterface extends RegistryInterface
{
    public const NAME = 'behavior';

    /**
     * Finds the behavior configuration by name or class.
     */
    public function find(string $behavior, bool $throwException = true): ?BehaviorConfig;
}

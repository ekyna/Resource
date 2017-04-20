<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

/**
 * Interface BehaviorBuilderInterface
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BehaviorBuilderInterface
{
    public const DI_TAG = 'ekyna_resource.behavior_builder';

    /**
     * Configures the behavior builder.
     * Keys 'name', 'label' and 'interface' must be defined and non empty.
     *
     * @return array
     */
    public static function configureBuilder(): array;
}

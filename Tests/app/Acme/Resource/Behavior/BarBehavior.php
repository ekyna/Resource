<?php

declare(strict_types=1);

namespace Acme\Resource\Behavior;

use Ekyna\Component\Resource\Behavior\AbstractBehavior;

/**
 * Class BarBehavior
 * @package Acme\Resource\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BarBehavior extends AbstractBehavior
{
    public static function configureBehavior(): array
    {
        return [];
    }
}

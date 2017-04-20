<?php

declare(strict_types=1);

namespace Acme\Resource\Behavior;

use Ekyna\Component\Resource\Behavior\AbstractBehavior;

/**
 * Class FooBehavior
 * @package Acme\Resource\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FooBehavior extends AbstractBehavior
{
    public static function configureBehavior(): array
    {
        return [];
    }
}

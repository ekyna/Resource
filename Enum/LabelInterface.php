<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface LabelInterface
 * @package Ekyna\Component\Resource\Enum
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface LabelInterface
{
    public function label(): TranslatableInterface;
}

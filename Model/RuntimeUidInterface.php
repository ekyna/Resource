<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Interface RuntimeUidInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RuntimeUidInterface
{
    public function getRuntimeUid(): string;
}

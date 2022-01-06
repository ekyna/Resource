<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Copier;

/**
 * Interface CopyInterface
 * @package Ekyna\Component\Resource\Copier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface CopyInterface
{
    public function onCopy(CopierInterface $copier): void;
}

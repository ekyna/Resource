<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Symfony\Component\Uid\Uuid;

/**
 * Trait RuntimeUidTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait RuntimeUidTrait
{
    protected ?string $runtimeUid = null;

    public function getRuntimeUid(): string
    {
        if (null !== $this->runtimeUid) {
            return $this->runtimeUid;
        }

        return $this->runtimeUid = Uuid::v4()->toRfc4122();
    }
}

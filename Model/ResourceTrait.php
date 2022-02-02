<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Trait ResourceTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ResourceTrait
{
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

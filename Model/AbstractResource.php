<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Class AbstractResource
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Make ResourceInterface implementations extends this
 */
class AbstractResource implements ResourceInterface
{
    private ?int $id = null;

    public function __clone()
    {
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

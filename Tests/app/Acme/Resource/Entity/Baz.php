<?php

declare(strict_types=1);

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\ResourceInterface;

class Baz implements ResourceInterface
{
    public function getId(): ?int
    {
        return null;
    }
}

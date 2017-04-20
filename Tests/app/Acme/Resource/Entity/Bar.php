<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslatable;

class Bar extends AbstractTranslatable
{
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}

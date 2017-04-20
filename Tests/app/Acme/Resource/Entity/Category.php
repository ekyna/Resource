<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\ResourceInterface;

class Category implements ResourceInterface
{
    private $id;
    private $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}

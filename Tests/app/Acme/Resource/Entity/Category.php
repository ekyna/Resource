<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\AbstractResource;

class Category extends AbstractResource
{
    private $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}

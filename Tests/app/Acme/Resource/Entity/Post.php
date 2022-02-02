<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslatable;

class Post extends AbstractTranslatable implements PostInterface
{
    /** @var Category */
    private $category;

    /** @var \DateTime */
    private $date;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): PostInterface
    {
        $this->category = $category;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): PostInterface
    {
        $this->date = $date;

        return $this;
    }
}

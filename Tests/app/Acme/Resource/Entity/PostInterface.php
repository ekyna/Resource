<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * @method PostTranslation translate($locale = null, $create = false)
 */
interface PostInterface extends TranslatableInterface
{
    public function getCategory(): ?Category;

    public function setCategory(Category $category): PostInterface;

    public function getDate(): ?\DateTime;

    public function setDate(\DateTime $date): PostInterface;
}
<?php

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AbstractTranslatable
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatable implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}

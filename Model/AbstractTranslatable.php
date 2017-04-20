<?php

declare(strict_types=1);

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
     * Clones the translatable.
     */
    public function __clone()
    {
        $translations = $this->translations->toArray();
        $this->translations = new ArrayCollection();
        foreach ($translations as $translation) {
            $this->addTranslation(clone $translation);
        }
    }
}

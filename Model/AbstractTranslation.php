<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Class AbstractTranslation
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * Clones the translation.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->translatable = null;
        }
    }
}

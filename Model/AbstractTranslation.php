<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Class AbstractTranslation
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslation extends AbstractResource implements TranslationInterface
{
    use TranslationTrait;

    public function __clone()
    {
        parent::__clone();

        $this->translatable = null;
    }
}

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
     * @var int
     */
    protected $id;


    /**
     * Clones the translation.
     */
    public function __clone()
    {
        $this->id = null;
        $this->translatable = null;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Class AbstractTranslation
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslation implements TranslationInterface
{
    use TranslationTrait;

    protected ?int $id = null;


    /**
     * Clones the translation.
     */
    public function __clone()
    {
        $this->id = null;
        $this->translatable = null;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}

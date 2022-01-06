<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Trait TranslationTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslationTrait
{
    protected ?TranslatableInterface $translatable = null;
    protected string                 $locale;

    public function getTranslatable(): ?TranslatableInterface
    {
        return $this->translatable;
    }

    public function setTranslatable(?TranslatableInterface $translatable): TranslationInterface
    {
        if ($translatable === $this->translatable) {
            return $this;
        }

        if ($previousTranslatable = $this->translatable) {
            $this->translatable = null;
            $previousTranslatable->removeTranslation($this);
        }

        if ($this->translatable = $translatable) {
            $this->translatable->addTranslation($this);
        }

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): TranslationInterface
    {
        $this->locale = $locale;

        return $this;
    }
}

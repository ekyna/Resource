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
    protected string $locale;


    /**
     * Returns the translatable.
     *
     * @return TranslatableInterface|null
     */
    public function getTranslatable(): ?TranslatableInterface
    {
        return $this->translatable;
    }

    /**
     * Sets the translatable.
     *
     * @param TranslatableInterface|null $translatable
     *
     * @return $this|TranslationInterface
     */
    public function setTranslatable(?TranslatableInterface $translatable): TranslationInterface
    {
        if ($translatable === $this->translatable) {
            return $this;
        }

        $previousTranslatable = $this->translatable;
        $this->translatable = $translatable;

        /** @var TranslationInterface $this */
        if (null !== $previousTranslatable) {
            $previousTranslatable->removeTranslation($this);
        }

        if (null !== $translatable) {
            $translatable->addTranslation($this);
        }

        return $this;
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|TranslationInterface
     */
    public function setLocale(string $locale): TranslationInterface
    {
        $this->locale = $locale;

        return $this;
    }
}

<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Trait TranslationTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslationTrait
{
    /**
     * @var TranslatableInterface
     */
    protected $translatable;

    /**
     * @var string
     */
    protected $locale;


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
    public function setTranslatable(TranslatableInterface $translatable = null): TranslationInterface
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
     * @return string|null
     */
    public function getLocale(): ?string
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

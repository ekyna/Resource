<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface TranslationInterface
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslationInterface extends ResourceInterface
{
    /**
     * Get the translatable object.
     *
     * @return TranslatableInterface|null
     */
    public function getTranslatable(): ?TranslatableInterface;

    /**
     * Set the translatable object.
     *
     * @param TranslatableInterface|null $translatable
     *
     * @return $this|TranslationInterface
     */
    public function setTranslatable(TranslatableInterface $translatable = null): TranslationInterface;

    /**
     * Get the locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return $this|TranslationInterface
     */
    public function setLocale(string $locale): TranslationInterface;
}

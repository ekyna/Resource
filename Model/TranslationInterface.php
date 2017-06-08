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
     * @return TranslatableInterface
     */
    public function getTranslatable();

    /**
     * Set the translatable object.
     *
     * @param null|TranslatableInterface $translatable
     *
     * @return self
     */
    public function setTranslatable(TranslatableInterface $translatable = null);

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale);
}

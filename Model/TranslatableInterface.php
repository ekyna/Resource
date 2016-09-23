<?php

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface TranslatableInterface
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableInterface extends ResourceInterface
{
    /**
     * Initializes the translations collection.
     *
     * @see \Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener::postLoad
     */
    public function initializeTranslations();

    /**
     * Returns the translation regarding to the current or fallback locale.
     *
     * @param string $locale
     * @param bool   $create
     * @return TranslationInterface
     * @throws \RuntimeException
     */
    public function translate($locale = null, $create = false);

    /**
     * Sets the current locale.
     *
     * @param string $locale
     * @return TranslatableInterface|$this
     */
    public function setCurrentLocale($locale);

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getCurrentLocale();

    /**
     * Sets the fallback locale.
     *
     * @param string $locale
     * @return TranslatableInterface|$this
     */
    public function setFallbackLocale($locale);

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale();

    /**
     * Adds the translation.
     *
     * @param TranslationInterface $translation
     * @return TranslatableInterface|$this
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * Removes the translation.
     *
     * @param TranslationInterface $translation
     * @return TranslatableInterface|$this
     */
    public function removeTranslation(TranslationInterface $translation);

    /**
     * Returns whether the translatable has the given translation.
     *
     * @param TranslationInterface $translation
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation);

    /**
     * Returns the translations.
     *
     * @return ArrayCollection|TranslationInterface[]
     */
    public function getTranslations();
}

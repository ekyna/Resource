<?php

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Interface TranslatableInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableInterface extends ResourceInterface
{
    /**
     * Initializes the translations collection.
     *
     * @see \Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener::postLoad
     */
    public function initializeTranslations(): void;

    /**
     * Returns the translation regarding to the current or fallback locale.
     *
     * @param string|null $locale
     * @param bool        $create
     *
     * @return TranslationInterface
     * @throws RuntimeException
     */
    public function translate(string $locale = null, bool $create = false): TranslationInterface;

    /**
     * Sets the current locale.
     *
     * @param string $locale
     *
     * @return TranslatableInterface|$this
     */
    public function setCurrentLocale(string $locale): TranslatableInterface;

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getCurrentLocale(): ?string;

    /**
     * Sets the fallback locale.
     *
     * @param string $locale
     *
     * @return TranslatableInterface|$this
     */
    public function setFallbackLocale(string $locale): TranslatableInterface;

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): ?string;

    /**
     * Adds the translation.
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface|$this
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface;

    /**
     * Removes the translation.
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface|$this
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface;

    /**
     * Returns whether the translatable has the given translation.
     *
     * @param TranslationInterface $translation
     *
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation): bool;

    /**
     * Returns whether a translation exists for the given locale.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslationForLocale(string $locale): bool;

    /**
     * Returns the translations.
     *
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations(): Collection;
}

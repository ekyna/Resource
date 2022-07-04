<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Interface TranslatableInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @template T of TranslationInterface
 */
interface TranslatableInterface extends ResourceInterface
{
    /**
     * Initializes the translation collection.
     *
     * @see \Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener::postLoad
     */
    public function initializeTranslations(): void;

    /**
     * Returns the translation regarding the current or fallback locale.
     *
     * @return T
     *
     * @throws RuntimeException
     */
    public function translate(string $locale = null, bool $create = false): TranslationInterface;

    /**
     * Sets the current locale.
     */
    public function setCurrentLocale(string $locale): TranslatableInterface;

    /**
     * Returns the current locale.
     */
    public function getCurrentLocale(): string;

    /**
     * Sets the fallback locale.
     */
    public function setFallbackLocale(string $locale): TranslatableInterface;

    /**
     * Returns the fallback locale.
     */
    public function getFallbackLocale(): string;

    /**
     * Adds the translation.
     *
     * @psalm-param T $translation
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface;

    /**
     * Removes the translation.
     *
     * @psalm-param T $translation
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface;

    /**
     * Returns whether the translatable has the given translation.
     *
     * @psalm-param T $translation
     */
    public function hasTranslation(TranslationInterface $translation): bool;

    /**
     * Returns whether a translation exists for the given locale.
     */
    public function hasTranslationForLocale(string $locale): bool;

    /**
     * Returns the translations.
     *
     * @return Collection<T>
     */
    public function getTranslations(): Collection;
}

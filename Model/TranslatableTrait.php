<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait TranslatableTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @template T of TranslationInterface
 */
trait TranslatableTrait
{
    protected string                $currentLocale;
    protected string                $fallbackLocale;
    protected ?TranslationInterface $currentTranslation = null;
    /**@var Collection<T>|null */
    protected ?Collection $translations = null;

    public function __construct()
    {
        $this->initializeTranslations();
    }

    /**
     * @see \Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener::postLoad
     */
    public function initializeTranslations(): void
    {
        if (null === $this->translations) {
            $this->translations = new ArrayCollection();
        }
    }

    /**
     * @return T
     */
    public function translate(string $locale = null, bool $create = false): TranslationInterface
    {
        $locale = $locale ?: $this->currentLocale;

        if ($this->currentTranslation && ($locale === $this->currentTranslation->getLocale())) {
            return $this->currentTranslation;
        }

        if (!$translation = $this->translations->get($locale)) {
            if ($fallbackTranslation = $this->translations->get($this->getFallbackLocale())) {
                $translation = clone $fallbackTranslation;
            } else {
                $className = $this->getTranslationClass();
                /** @var TranslationInterface $translation */
                $translation = new $className();
            }

            $translation->setLocale($locale);

            if ($create || ($this->fallbackLocale === $locale)) {
                $this->addTranslation($translation);
            }
        }

        if ($this->currentLocale && $this->currentLocale === $locale) {
            $this->currentTranslation = $translation;
        }

        return $translation;
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    public function setCurrentLocale(string $locale): TranslatableInterface
    {
        $this->currentLocale = $locale;

        return $this;
    }

    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    public function setFallbackLocale(string $locale): TranslatableInterface
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * @psalm-param T $translation
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->containsKey($translation->getLocale())) {
            $this->translations->set($translation->getLocale(), $translation);
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * @psalm-param T $translation
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if ($this->translations->removeElement($translation)) {
            $translation->setTranslatable(null);
        }

        return $this;
    }

    /**
     * @psalm-param T $translation
     */
    public function hasTranslation(TranslationInterface $translation): bool
    {
        return $this->hasTranslationForLocale($translation->getLocale());
    }

    public function hasTranslationForLocale(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @return Collection<T>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @TODO Translation class may be overridden by resource configuration files.
     *       Use resource registry to get proper translation class.
     * @deprecated
     */
    protected function getTranslationClass(): string
    {
        return get_class($this) . 'Translation';
    }
}

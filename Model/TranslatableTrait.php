<?php

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Trait TranslatableTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableTrait
{
    /**
     * @var string
     */
    protected $currentLocale;

    /**
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @var TranslationInterface
     */
    protected $currentTranslation;

    /**
     * @var Collection|TranslationInterface[]
     */
    protected $translations;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeTranslations();
    }

    /**
     * Initializes the translations collection.
     *
     * @see \Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener::postLoad
     */
    public function initializeTranslations(): void
    {
        if (null === $this->translations) {
            $this->translations = new ArrayCollection();
        }
    }

    /**
     * Returns the translation regarding to the current or fallback locale.
     *
     * @param string|null $locale
     * @param bool $create
     *
     * @return TranslationInterface
     * @throws RuntimeException
     */
    public function translate(string $locale = null, bool $create = false): TranslationInterface
    {
        $locale = $locale ?: $this->currentLocale;
        if (null === $locale) {
            throw new RuntimeException('No locale has been set and current locale is undefined.');
        }

        if ($this->currentTranslation && ($locale === $this->currentTranslation->getLocale())) {
            return $this->currentTranslation;
        }

        if (!$translation = $this->translations->get($locale)) {
            if (null === $this->fallbackLocale) {
                throw new RuntimeException('No fallback locale has been set.');
            }

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

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }

    /**
     * Sets the current locale.
     *
     * @param string $locale
     *
     * @return TranslatableInterface|$this
     */
    public function setCurrentLocale(string $locale): TranslatableInterface
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): ?string
    {
        return $this->fallbackLocale;
    }

    /**
     * Sets the fallback locale.
     *
     * @param string $locale
     *
     * @return TranslatableInterface|$this
     */
    public function setFallbackLocale(string $locale): TranslatableInterface
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Adds the translation.
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface|$this
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->containsKey($translation->getLocale())) {
            $this->translations->set($translation->getLocale(), $translation);
            /** @noinspection PhpParamsInspection */
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Removes the translation.
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface|$this
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if ($this->translations->removeElement($translation)) {
            $translation->setTranslatable(null);
        }

        return $this;
    }

    /**
     * Returns whether the translatable has the given translation.
     *
     * @param TranslationInterface $translation
     *
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation): bool
    {
        return $this->hasTranslationForLocale($translation->getLocale());
    }

    /**
     * Returns whether a translation exists for the given locale.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslationForLocale(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * Returns the translations.
     *
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * Return translation model class.
     *
     * @return string
     */
    protected function getTranslationClass(): string
    {
        return get_class($this) . 'Translation';
    }
}

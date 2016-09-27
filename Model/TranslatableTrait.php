<?php

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait TranslatableTrait
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
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
     * @var ArrayCollection|TranslationInterface[]
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
    public function initializeTranslations()
    {
        if (null === $this->translations) {
            $this->translations = new ArrayCollection();
        }
    }

    /**
     * Returns the translation regarding to the current or fallback locale.
     *
     * @param string $locale
     * @param bool   $create
     * @return TranslationInterface
     * @throws \RuntimeException
     */
    public function translate($locale = null, $create = false)
    {
        $locale = $locale ?: $this->currentLocale;
        if (null === $locale) {
            throw new \RuntimeException('No locale has been set and current locale is undefined.');
        }

        if ($this->currentTranslation && $locale === $this->currentTranslation->getLocale()) {
            return $this->currentTranslation;
        }

        if (!$translation = $this->translations->get($locale)) {
            if ($create) {
                $className = $this->getTranslationClass();

                /** @var TranslationInterface $translation */
                $translation = new $className();
                $translation->setLocale($locale);

                $this->addTranslation($translation);
            } else {
                if (null === $this->fallbackLocale) {
                    throw new \RuntimeException('No fallback locale has been set.');
                }

                if (!$fallbackTranslation = $this->translations->get($this->getFallbackLocale())) {
                    $className = $this->getTranslationClass();

                    /** @var TranslationInterface $translation */
                    $translation = new $className();
                    $translation->setLocale($locale);

                    $this->addTranslation($translation);
                } else {
                    $translation = clone $fallbackTranslation;
                }
            }
        }

        $this->currentTranslation = $translation;
        $this->currentLocale      = $locale;

        return $translation;
    }

    /**
     * Sets the current locale.
     *
     * @param string $locale
     * @return TranslatableInterface|$this
     */
    public function setCurrentLocale($locale)
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Sets the fallback locale.
     *
     * @param string $locale
     * @return TranslatableInterface|$this
     */
    public function setFallbackLocale($locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * Adds the translation.
     *
     * @param TranslationInterface $translation
     * @return TranslatableInterface|$this
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->containsKey($translation->getLocale())) {
            $this->translations->set($translation->getLocale(), $translation);
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Removes the translation.
     *
     * @param TranslationInterface $translation
     * @return TranslatableInterface|$this
     */
    public function removeTranslation(TranslationInterface $translation)
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
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation)
    {
        return $this->translations->containsKey($translation->getLocale());
    }

    /**
     * Returns the translations.
     *
     * @return ArrayCollection|TranslationInterface[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Return translation model class.
     *
     * @return string
     */
    protected function getTranslationClass()
    {
        return get_class($this) . 'Translation';
    }
}

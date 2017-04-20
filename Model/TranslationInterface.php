<?php

declare(strict_types=1);

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
    public function setTranslatable(?TranslatableInterface $translatable): TranslationInterface;

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return $this|TranslationInterface
     */
    public function setLocale(string $locale): TranslationInterface;
}

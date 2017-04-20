<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Locale;

/**
 * Interface LocaleProviderInterface
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface LocaleProviderInterface
{
    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getCurrentLocale(): string;

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): string;

    /**
     * Returns the available locales.
     *
     * @return array
     */
    public function getAvailableLocales(): array;
}

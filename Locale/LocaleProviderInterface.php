<?php

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
    public function getCurrentLocale();

    /**
     * Returns the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale();

    /**
     * Returns the available locales.
     *
     * @return array
     */
    public function getAvailableLocales();
}

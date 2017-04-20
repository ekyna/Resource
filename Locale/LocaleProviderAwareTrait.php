<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Locale;

/**
 * Class LocaleProviderAwareTrait
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait LocaleProviderAwareTrait
{
    protected ?LocaleProviderInterface $localeProvider;

    /**
     * Returns the locale provider.
     *
     * @return LocaleProviderInterface
     */
    public function getLocaleProvider(): LocaleProviderInterface
    {
        return $this->localeProvider;
    }

    /**
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider): void
    {
        $this->localeProvider = $localeProvider;
    }
}

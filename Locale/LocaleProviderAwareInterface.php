<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Locale;

/**
 * Interface LocaleProviderAwareInterface
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface LocaleProviderAwareInterface
{
    /**
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $provider
     */
    public function setLocaleProvider(LocaleProviderInterface $provider): void;
}

<?php

namespace Ekyna\Component\Resource\Locale;

/**
 * Class LocaleProvider
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var string
     */
    private $currentLocale;

    /**
     * @var string
     */
    private $fallbackLocale;

    /**
     * @var array
     */
    private $availableLocales;


    /**
     * Constructor.
     *
     * @param string $currentLocale
     * @param string $fallbackLocale
     * @param array  $availableLocales
     */
    public function __construct($currentLocale, $fallbackLocale, array $availableLocales)
    {
        $this->currentLocale = $currentLocale;
        $this->fallbackLocale = $fallbackLocale;
        $this->availableLocales = $availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }
}

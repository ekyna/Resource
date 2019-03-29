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
     * @var array
     */
    protected $availableLocales;

    /**
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @var string
     */
    protected $currentLocale;


    /**
     * Constructor.
     *
     * @param array  $availableLocales
     * @param string $fallbackLocale
     * @param string $currentLocale
     */
    public function __construct(array $availableLocales, $fallbackLocale, $currentLocale = null)
    {
        $this->availableLocales = $availableLocales;
        $this->fallbackLocale = $fallbackLocale;
        $this->currentLocale = $currentLocale;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentLocale()
    {
        if ($this->currentLocale) {
            return $this->currentLocale;
        }

        return $this->getFallbackLocale();
    }

    /**
     * @inheritdoc
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Locale;

/**
 * Class LocaleProvider
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LocaleProvider implements LocaleProviderInterface
{
    protected array   $availableLocales;
    protected string  $fallbackLocale;
    protected ?string $currentLocale = null;


    /**
     * Constructor.
     *
     * @param array       $availableLocales
     * @param string      $fallbackLocale
     * @param string|null $currentLocale
     */
    public function __construct(array $availableLocales, string $fallbackLocale, string $currentLocale = null)
    {
        $this->availableLocales = $availableLocales;
        $this->fallbackLocale = $fallbackLocale;
        $this->currentLocale = $currentLocale;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentLocale(): string
    {
        if ($this->currentLocale) {
            return $this->currentLocale;
        }

        return $this->getFallbackLocale();
    }

    /**
     * @inheritDoc
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }
}

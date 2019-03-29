<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Trait LocalizedTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait LocalizedTrait
{
    /**
     * @var string
     */
    protected $locale;


    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|LocalizedInterface
     */
    public function setLocale(?string $locale)
    {
        $this->locale = $locale;

        return $this;
    }
}

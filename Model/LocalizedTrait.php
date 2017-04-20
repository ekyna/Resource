<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Trait LocalizedTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait LocalizedTrait
{
    protected ?string $locale = null;


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
     * @param string|null $locale
     *
     * @return $this|LocalizedInterface
     */
    public function setLocale(?string $locale): LocalizedInterface
    {
        $this->locale = $locale;

        return $this;
    }
}

<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface LocalizedInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface LocalizedInterface
{
    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): ?string;
}
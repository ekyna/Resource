<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Interface LocalizedInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Use \Symfony\Contracts\Translation\LocaleAwareInterface ?
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

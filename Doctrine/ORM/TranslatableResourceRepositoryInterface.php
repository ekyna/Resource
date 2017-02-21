<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Interface TranslatableResourceRepositoryInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableResourceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $localeProvider
     *
     * @return $this|TranslatableResourceRepositoryInterface
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider);

    /**
     * Sets the translatable fields.
     *
     * @param array $translatableFields
     *
     * @return $this|TranslatableResourceRepositoryInterface
     */
    public function setTranslatableFields(array $translatableFields);
}

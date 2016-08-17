<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;

/**
 * Interface TranslatableResourceRepositoryInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableResourceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @param LocaleProviderInterface $localeProvider
     *
     * @return self
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider);

    /**
     * @param array $translatableFields
     *
     * @return self
     */
    public function setTranslatableFields(array $translatableFields);
}

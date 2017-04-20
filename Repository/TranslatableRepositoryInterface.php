<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;

/**
 * Interface TranslatableRepositoryInterface
 * @package Ekyna\Component\Resource\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableRepositoryInterface extends LocaleProviderAwareInterface, ResourceRepositoryInterface
{
    /**
     * Sets the translatable fields.
     *
     * @param array $fields
     *
     * @return $this|TranslatableRepositoryInterface
     */
    public function setTranslatableFields(array $fields): TranslatableRepositoryInterface;
}

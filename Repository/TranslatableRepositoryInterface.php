<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;

/**
 * Interface TranslatableRepositoryInterface
 * @package Ekyna\Component\Resource\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T of TranslatableInterface
 * @extends ResourceRepositoryInterface<T>
 */
interface TranslatableRepositoryInterface extends LocaleProviderAwareInterface, ResourceRepositoryInterface
{
    /**
     * Sets the translatable fields.
     */
    public function setTranslatableFields(array $fields): TranslatableRepositoryInterface;
}

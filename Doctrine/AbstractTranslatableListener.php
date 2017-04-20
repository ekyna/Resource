<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\PropertyAccess as PA;

/**
 * Class AbstractTranslatableListener
 * @package Ekyna\Component\Resource\Doctrine
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatableListener
{
    protected ResourceRegistryInterface $registry;
    protected LocaleProviderInterface   $localeProvider;
    /**
     * Translation mapping = [
     *     Translatable class => Translation class,
     *     Translation class  => Translatable class,
     * ]
     */
    protected array $translations;

    private ?PA\PropertyAccessor $propertyAccessor = null;

    /**
     * Constructor.
     *
     * @param ResourceRegistryInterface $registry
     * @param LocaleProviderInterface   $localeProvider
     * @param array                     $translations
     */
    public function __construct(
        ResourceRegistryInterface $registry,
        LocaleProviderInterface $localeProvider,
        array $translations
    ) {
        $this->registry = $registry;
        $this->localeProvider = $localeProvider;
        $this->translations = $translations;
    }

    /**
     * Returns the property accessor.
     *
     * @return PA\PropertyAccessor
     */
    protected function getPropertyAccessor(): PA\PropertyAccessor
    {
        if (null !== $this->propertyAccessor) {
            return $this->propertyAccessor;
        }

        return $this->propertyAccessor = PA\PropertyAccess::createPropertyAccessor();
    }
}

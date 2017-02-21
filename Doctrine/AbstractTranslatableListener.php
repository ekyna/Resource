<?php

namespace Ekyna\Component\Resource\Doctrine;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Symfony\Component\PropertyAccess as PA;

/**
 * Class AbstractTranslatableListener
 * @package Ekyna\Component\Resource\Doctrine
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatableListener
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * Translation mapping = [
     *     Translatable class => Translation class,
     *     Translation class  => Translatable class,
     * ]
     *
     * @var array
     */
    protected $configs;

    /**
     * @var PA\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry   $registry
     * @param LocaleProviderInterface $localeProvider
     * @param array                   $configs
     */
    public function __construct(
        ConfigurationRegistry $registry,
        LocaleProviderInterface $localeProvider,
        array $configs
    ) {
        $this->registry = $registry;
        $this->localeProvider = $localeProvider;
        $this->configs = $configs;
    }

    /**
     * Returns the property accessor.
     *
     * @return PA\PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (null !== $this->propertyAccessor) {
            return $this->propertyAccessor;
        }

        return $this->propertyAccessor = PA\PropertyAccess::createPropertyAccessor();
    }
}

<?php

namespace Ekyna\Component\Resource\Doctrine;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;

/**
 * Class AbstractTranslatableListener
 * @package Ekyna\Component\Resource\Doctrine
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatableListener
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * Mapping.
     *
     * @var array
     */
    protected $configs;

    public function __construct(LocaleProviderInterface $localeProvider, array $configs)
    {
        $this->localeProvider = $localeProvider;
        $this->configs = $configs;
    }
}

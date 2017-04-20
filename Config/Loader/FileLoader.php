<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;

/**
 * Class FileLoader
 * @package Ekyna\Component\Resource\Config\Loader
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class FileLoader extends BaseFileLoader
{
    protected ConfigLoader $loader;


    /**
     * Constructor.
     *
     * @param ConfigLoader         $loader A RegistryBuilder instance
     * @param FileLocatorInterface $locator  A FileLocator instance
     */
    public function __construct(ConfigLoader $loader, FileLocatorInterface $locator)
    {
        $this->loader = $loader;

        parent::__construct($locator);
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Factory;

use Ekyna\Component\Resource\Locale\LocaleProviderAwareInterface;

/**
 * Class TranslatableFactoryInterface
 * @package Ekyna\Component\Resource\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableFactoryInterface extends ResourceFactoryInterface, LocaleProviderAwareInterface
{

}

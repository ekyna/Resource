<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Factory;

use Ekyna\Component\Resource\Config\ResourceConfig;

/**
 * Interface AdapterInterface
 * @package Ekyna\Component\Resource\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AdapterInterface
{
    public function createFactory(ResourceConfig $config): ResourceFactoryInterface;

    public function supports(ResourceConfig $config): bool;
}

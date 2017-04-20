<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Manager;

use Ekyna\Component\Resource\Config\ResourceConfig;

/**
 * Interface AdapterInterface
 * @package Ekyna\Component\Resource\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AdapterInterface
{
    public function createManager(ResourceConfig $config): ResourceManagerInterface;

    public function supports(ResourceConfig $config): bool;
}

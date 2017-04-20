<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

use Ekyna\Component\Resource\Config\ResourceConfig;

/**
 * Interface AdapterInterface
 * @package Ekyna\Component\Resource\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AdapterInterface
{
    public function createRepository(ResourceConfig $config): ResourceRepositoryInterface;

    public function supports(ResourceConfig $config): bool;
}

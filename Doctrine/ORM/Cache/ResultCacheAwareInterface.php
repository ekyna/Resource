<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Cache;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Interface ResultCacheAwareInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ResultCacheAwareInterface
{
    public function setResultCache(CacheItemPoolInterface $resultCache): void;
}

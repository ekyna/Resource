<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Cache;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Trait ResultCacheAwareTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ResultCacheAwareTrait
{
    private ?CacheItemPoolInterface $resultCache = null;

    public function setResultCache(CacheItemPoolInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    protected function getResultCache(): ?CacheItemPoolInterface
    {
        return $this->resultCache;
    }
}

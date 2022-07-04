<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Cache;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * Trait ResultCacheAwareTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ResultCacheAwareTrait
{
    private CacheInterface $resultCache;

    public function setResultCache(CacheInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    protected function getResultCache(): CacheInterface
    {
        return $this->resultCache;
    }
}

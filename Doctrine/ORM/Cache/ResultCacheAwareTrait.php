<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Cache;

use Doctrine\Common\Cache\Cache;

/**
 * Trait ResultCacheAwareTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ResultCacheAwareTrait
{
    private Cache $resultCache;

    public function setResultCache(Cache $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    protected function getResultCache(): Cache
    {
        return $this->resultCache;
    }
}

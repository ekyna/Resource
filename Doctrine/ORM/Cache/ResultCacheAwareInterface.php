<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Cache;

use Doctrine\Common\Cache\Cache;

/**
 * Interface ResultCacheAwareInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ResultCacheAwareInterface
{
    public function setResultCache(Cache $resultCache): void;
}

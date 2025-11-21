<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine;

use Doctrine\Common\Collections\Collection;

use function method_exists;

/**
 * Class DoctrineUtil
 * @package Ekyna\Component\Resource\Doctrine
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineUtil
{
    public static function isInitializedCollection(?Collection $collection): bool
    {
        return (null !== $collection)
            && method_exists($collection, 'isInitialized')
            && $collection->{'isInitialized'}();
    }
}

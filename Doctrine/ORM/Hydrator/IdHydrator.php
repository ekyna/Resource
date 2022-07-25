<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * Class IdHydrator
 * @package Ekyna\Component\Resource\Doctrine\ORM\Hydrator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdHydrator extends AbstractHydrator
{
    public const NAME = 'IdHydrator';

    protected function hydrateAllData(): array
    {
        return array_map(fn(string $id): int => (int)$id, $this->statement()->fetchFirstColumn());
    }
}

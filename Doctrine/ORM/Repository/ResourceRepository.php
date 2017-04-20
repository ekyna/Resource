<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class ResourceRepository
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository implements ResourceRepositoryInterface
{
    use ResourceRepositoryTrait;
}

<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Resource\Doctrine\ORM\Util\ResourceRepositoryTrait;

/**
 * Class ResourceRepository
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository extends EntityRepository implements ResourceRepositoryInterface
{
    use ResourceRepositoryTrait;
}

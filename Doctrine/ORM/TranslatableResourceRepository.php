<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Resource\Doctrine\ORM\Util\TranslatableResourceRepositoryTrait;

/**
 * Class TranslatableResourceRepository
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableResourceRepository extends EntityRepository implements TranslatableResourceRepositoryInterface
{
    use TranslatableResourceRepositoryTrait;
}

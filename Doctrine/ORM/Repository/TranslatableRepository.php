<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Class TranslatableResourceRepository
 * @package Ekyna\Component\Resource\Doctrine\Repository
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableRepository implements TranslatableRepositoryInterface
{
    use TranslatableRepositoryTrait;
}

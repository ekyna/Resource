<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Copier;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CopierInterface
 * @package Ekyna\Component\Resource\Copier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface CopierInterface
{
    /***
     * @param T $resource
     *
     * @return T
     */
    public function copyResource(ResourceInterface $resource): ResourceInterface;

    public function copyCollection(ResourceInterface $resource, string $property, bool $deep): void;
}

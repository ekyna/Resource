<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Copier;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Copier
 * @package Ekyna\Component\Resource\Copier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Copier implements CopierInterface
{
    public function copyResource(ResourceInterface $resource): ResourceInterface
    {
        $copy = clone $resource;

        if ($copy instanceof CopyInterface) {
            $copy->onCopy($this);
        }

        return $copy;
    }

    public function copyCollection(Collection $collection, bool $deep): Collection
    {
        $copy = new ArrayCollection();

        foreach ($collection->toArray() as $item) {
            if ($deep && $item instanceof ResourceInterface) {
                $item = $this->copyResource($item);
            }

            $copy->add($item);
        }

        return $copy;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Copier;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class Copier
 * @package Ekyna\Component\Resource\Copier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Copier implements CopierInterface
{
    private ?PropertyAccessorInterface $accessor = null;

    public function copyResource(ResourceInterface $resource): ResourceInterface
    {
        $copy = clone $resource;

        if ($copy instanceof CopyInterface) {
            $copy->onCopy($this);
        }

        return $copy;
    }

    public function copyCollection(ResourceInterface $resource, string $property, bool $deep): void
    {
        $accessor = $this->getAccessor();

        $collection = $accessor->getValue($resource, $property);

        if (!$collection instanceof Collection) {
            throw new UnexpectedTypeException($collection, Collection::class);
        }

        $sourceItems = $collection->toArray();
        $collection->clear();

        $copiedItems = [];

        foreach ($sourceItems as $item) {
            if ($deep) {
                if (!$item instanceof ResourceInterface) {
                    throw new UnexpectedTypeException($item, ResourceInterface::class);
                }

                $item = $this->copyResource($item);
            }

            $copiedItems[] = $item;
        }

        $accessor->setValue($resource, $property, $copiedItems);
    }

    private function getAccessor(): PropertyAccessorInterface
    {
        if ($this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}

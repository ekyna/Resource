<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Copier;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_class;

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

        $this->initializeCollection($resource, $property);

        $copiedItems = [];

        foreach ($collection as $item) {
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

    private function initializeCollection(ResourceInterface $resource, string $property): void
    {
        $refProp = new ReflectionProperty(ClassUtils::getRealClass(get_class($resource)), $property);
        $refProp->setValue($resource, new ArrayCollection());
    }
}

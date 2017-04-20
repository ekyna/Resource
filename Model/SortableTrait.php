<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Trait SortableTrait
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait SortableTrait
{
    protected int $position = 0;


    /**
     * @return SortableInterface|$this
     */
    public function setPosition(int $position): SortableInterface
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     *
     * @see https://github.com/Atlantic18/DoctrineExtensions/issues/1726
     */
    public function compareTo($other)
    {
        if (get_class($other) === static::class) {
            /** @var SortableInterface $other */
            return $this->position - $other->getPosition();
        }

        return 0;
    }
}

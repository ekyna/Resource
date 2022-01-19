<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use const INF;

/**
 * Trait SortableTrait
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait SortableTrait
{
    protected int $position = -1;

    /**
     * @return SortableInterface|$this
     */
    public function setPosition(int $position): SortableInterface
    {
        $this->position = max(0, $position);

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
        if (get_class($other) !== static::class) {
            return 0;
        }

        /** @var SortableInterface $other */
        return $this->getComparedValue() <=> $other->getComparedValue();
    }

    private function getComparedValue(): int
    {
        return -1 === $this->position ? INF : $this->position;
    }
}

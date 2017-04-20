<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Comparable;

/**
 * Interface SortableInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SortableInterface extends Comparable
{
    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return SortableInterface|$this
     */
    public function setPosition(int $position): SortableInterface;

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition(): int;
}

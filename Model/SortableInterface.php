<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface SortableInterface
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface SortableInterface
{
    /**
     * Sets the position.
     *
     * @param integer $position
     * @return SortableInterface|$this
     */
    public function setPosition($position);

    /**
     * Returns the position.
     *
     * @return integer
     */
    public function getPosition();
}

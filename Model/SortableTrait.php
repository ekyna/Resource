<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Trait SortableTrait
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait SortableTrait
{
    /**
     * @var integer
     */
    protected $position;


    /**
     * Sets the position.
     *
     * @param integer $position
     * @return SortableInterface|$this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Returns the position.
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }
}

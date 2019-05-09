<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Trait IsDefaultTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait IsDefaultTrait
{
    /**
     * @var bool
     */
    protected $default = false;


    /**
     * Sets whether the resource is default or not.
     *
     * @param bool $default
     *
     * @return $this|IsDefaultInterface
     */
    public function setDefault(bool $default): IsDefaultInterface
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Returns whether the resource is default or not.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }
}

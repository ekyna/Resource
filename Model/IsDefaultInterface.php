<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface IsDefaultInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface IsDefaultInterface extends ResourceInterface
{
    /**
     * Sets whether the resource is default or not.
     *
     * @param bool $default
     *
     * @return $this|IsDefaultInterface
     */
    public function setDefault(bool $default): IsDefaultInterface;

    /**
     * Returns whether the resource is default or not.
     *
     * @return bool
     */
    public function isDefault() : bool;
}

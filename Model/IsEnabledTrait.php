<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Trait IsEnabledTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait IsEnabledTrait
{
    /**
     * @var bool
     */
    protected $enabled;


    /**
     * Sets whether the resource is enabled or not.
     *
     * @param bool $enabled
     *
     * @return $this|IsEnabledInterface
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * Returns whether the resource is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}

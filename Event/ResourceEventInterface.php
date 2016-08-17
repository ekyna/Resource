<?php

namespace Ekyna\Component\Resource\Event;

/**
 * Interface ResourceEventInterface
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventInterface
{
    /**
     * Returns the resource.
     *
     * @return \Ekyna\Component\Resource\Model\ResourceInterface
     */
    public function getResource();
}

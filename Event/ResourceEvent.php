<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ResourceEvent
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvent extends Event implements ResourceEventInterface
{
    /**
     * @var ResourceInterface
     */
    protected $resource;


    /**
     * Returns the resource.
     *
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the resource.
     *
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
}

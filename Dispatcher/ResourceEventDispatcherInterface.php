<?php

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ResourceEventDispatcherInterface
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param string                                   $eventName
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    public function scheduleEvent($eventName, $resourceOrEvent);

    /**
     * Creates the resource event.
     *
     * @param ResourceInterface $resource
     * @param bool              $throwException
     *
     * @return ResourceEventInterface|null
     */
    public function createResourceEvent(ResourceInterface $resource, $throwException = true);

    /**
     * Returns the resource event name.
     *
     * @param ResourceInterface $resource
     * @param string            $suffix
     *
     * @return string|null
     */
    public function getResourceEventName(ResourceInterface $resource, $suffix);
}

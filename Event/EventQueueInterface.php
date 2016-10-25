<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface EventQueueInterface
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface EventQueueInterface
{
    /**
     * Sets whether the queue is opened or not.
     *
     * @param boolean $opened
     */
    public function setOpened($opened);

    /**
     * Schedules the resource event.
     *
     * @param string                                   $eventName
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    public function scheduleEvent($eventName, $resourceOrEvent);

    /**
     * Flushes the event queue.
     */
    public function flush();
}

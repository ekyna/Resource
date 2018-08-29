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
     * Sets whether or not the queue is opened.
     *
     * @param bool $opened
     */
    public function setOpened($opened);

    /**
     * Returns whether or not the queue is opened.
     *
     * @return bool
     */
    public function isOpened();

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

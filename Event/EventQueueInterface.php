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
     * Schedules an insert resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleInsert(ResourceInterface $resource);

    /**
     * Schedules an insert resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleUpdate(ResourceInterface $resource);

    /**
     * Schedules an insert resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleDelete(ResourceInterface $resource);

    /**
     * Flushes the event queue.
     */
    public function flush();
}

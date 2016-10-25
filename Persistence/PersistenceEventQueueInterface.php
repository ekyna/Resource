<?php

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PersistenceEventQueueInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceEventQueueInterface extends EventQueueInterface
{
    /**
     * Schedules the insert resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleInsert(ResourceInterface $resource);

    /**
     * Schedules the update resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleUpdate(ResourceInterface $resource);

    /**
     * Schedules the delete resource event.
     *
     * @param ResourceInterface $resource
     */
    public function scheduleDelete(ResourceInterface $resource);
}

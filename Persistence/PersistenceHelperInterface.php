<?php

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PersistenceHelperInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceHelperInterface
{
    /**
     * Returns the manager.
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getManager();

    /**
     * Returns the entity change set.
     *
     * @param ResourceInterface $resource
     * @param string            $property
     *
     * @return array
     */
    public function getChangeSet(ResourceInterface $resource, $property = null);

    /**
     * Returns whether at least one of the given properties has changed.
     *
     *
     * @param ResourceInterface $resource
     * @param string|array      $properties
     *
     * @return bool
     */
    public function isChanged(ResourceInterface $resource, $properties);

    /**
     * Returns whether or not the resource is scheduled for insert.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForInsert(ResourceInterface $resource);

    /**
     * Returns whether or not the resource is scheduled for update.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForUpdate(ResourceInterface $resource);

    /**
     * Returns whether or not the resource is scheduled for remove.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForRemove(ResourceInterface $resource);

    /**
     * Persists and recompute the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $schedule
     */
    public function persistAndRecompute(ResourceInterface $resource, $schedule = false);

    /**
     * Removes the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $schedule
     */
    public function remove(ResourceInterface $resource, $schedule = false);

    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param string                                   $eventName
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    public function scheduleEvent($eventName, $resourceOrEvent);
}

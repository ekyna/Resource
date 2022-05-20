<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
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
     * @param string|null $entityClass
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     *
     * @deprecated Use manager service directly
     * @TODO       Remove / Break doctrine dependency
     */
    public function getManager(string $entityClass = null);

    /**
     * Returns the persistence event queue.
     *
     * @return PersistenceEventQueueInterface
     */
    public function getEventQueue(): PersistenceEventQueueInterface;

    /**
     * Returns the entity change set.
     *
     * @param ResourceInterface $resource
     * @param array|string|null $property
     *
     * @return array
     */
    public function getChangeSet(ResourceInterface $resource, $property = null): array;

    /**
     * Returns whether at least one of the given properties has changed.
     *
     * @param ResourceInterface $resource
     * @param string|array      $properties
     *
     * @return bool
     */
    public function isChanged(ResourceInterface $resource, $properties): bool;

    /**
     * Returns whether the resource property has changed from the given value.
     *
     * @param mixed $from
     */
    public function isChangedFrom(ResourceInterface $resource, string $property, $from): bool;

    /**
     * Returns whether the resource property has changed to the given value.
     *
     * @param mixed $to
     */
    public function isChangedTo(ResourceInterface $resource, string $property, $to): bool;

    /**
     * Returns whether the resource property has changed from and to the given values.
     *
     * @param mixed $from
     * @param mixed $to
     */
    public function isChangedFromTo(ResourceInterface $resource, string $property, $from, $to): bool;

    /**
     * Returns whether the resource is scheduled for insert.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForInsert(ResourceInterface $resource): bool;

    /**
     * Returns whether the resource is scheduled for update.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForUpdate(ResourceInterface $resource): bool;

    /**
     * Returns whether the resource is scheduled for remove.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isScheduledForRemove(ResourceInterface $resource): bool;

    /**
     * Persists and recompute the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $schedule
     */
    public function persistAndRecompute(ResourceInterface $resource, bool $schedule = false): void;

    /**
     * Removes the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $schedule
     */
    public function remove(ResourceInterface $resource, bool $schedule = false): void;

    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     * @param string                                   $eventName
     *
     * @throws ResourceExceptionInterface
     */
    public function scheduleEvent(object $resourceOrEvent, string $eventName): void;
}

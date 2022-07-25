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
     */
    public function getEventQueue(): PersistenceEventQueueInterface;

    /**
     * Returns the entity change set.
     */
    public function getChangeSet(ResourceInterface $resource, array|string $property = null): array;

    /**
     * Returns whether at least one of the given properties has changed.
     */
    public function isChanged(ResourceInterface $resource, string|array $properties): bool;

    /**
     * Returns whether the resource property has changed from the given value.
     */
    public function isChangedFrom(ResourceInterface $resource, string $property, mixed $from): bool;

    /**
     * Returns whether the resource property has changed to the given value.
     */
    public function isChangedTo(ResourceInterface $resource, string $property, mixed $to): bool;

    /**
     * Returns whether the resource property has changed from and to the given values.
     */
    public function isChangedFromTo(ResourceInterface $resource, string $property, mixed $from, mixed $to): bool;

    /**
     * Returns whether the resource is scheduled for insert.
     */
    public function isScheduledForInsert(ResourceInterface $resource): bool;

    /**
     * Returns whether the resource is scheduled for update.
     */
    public function isScheduledForUpdate(ResourceInterface $resource): bool;

    /**
     * Returns whether the resource is scheduled for remove.
     */
    public function isScheduledForRemove(ResourceInterface $resource): bool;

    /**
     * Persists and recompute the given resource.
     */
    public function persistAndRecompute(ResourceInterface $resource, bool $schedule = false): void;

    /**
     * Removes the given resource.
     */
    public function remove(ResourceInterface $resource, bool $schedule = false): void;

    /**
     * Schedules the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @throws ResourceExceptionInterface
     */
    public function scheduleEvent(ResourceInterface|ResourceEventInterface $resourceOrEvent, string $eventName): void;

    /**
     * Clears the resource event.
     */
    public function clearEvent(ResourceInterface $resource, string $eventName): void;
}

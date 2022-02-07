<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

use function array_key_exists;
use function gettype;

/**
 * Class PersistenceHelper
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceHelper implements PersistenceHelperInterface
{
    protected ManagerRegistry $registry;
    protected PersistenceTrackerInterface $tracker;
    protected PersistenceEventQueueInterface $eventQueue;

    public function __construct(
        ManagerRegistry $registry,
        PersistenceTrackerInterface $tracker,
        PersistenceEventQueueInterface $eventQueue
    ) {
        $this->registry = $registry;
        $this->tracker = $tracker;
        $this->eventQueue = $eventQueue;
    }

    /**
     * @inheritDoc
     */
    public function getEventQueue(): PersistenceEventQueueInterface
    {
        return $this->eventQueue;
    }

    /**
     * @inheritDoc
     */
    public function getChangeSet(ResourceInterface $resource, string $property = null): array
    {
        return $this->tracker->getChangeSet($resource, $property);
    }

    /**
     * @inheritDoc
     */
    public function isChanged(ResourceInterface $resource, $properties): bool
    {
        $changeSet = $this->getChangeSet($resource);

        if (is_string($properties)) {
            return isset($changeSet[$properties]) || array_key_exists($properties, $changeSet);
        } elseif (is_array($properties)) {
            return !empty(array_intersect($properties, array_keys($changeSet)));
        }

        throw new UnexpectedTypeException($properties, ['string', 'array']);
    }

    /**
     * @param mixed $from
     */
    public function isChangedFrom(ResourceInterface $resource, string $property, $from): bool
    {
        $changeSet = $this->getChangeSet($resource, $property);

        return array_key_exists(0, $changeSet) && $this->isEqual($changeSet[0], $from);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     */
    private function isEqual($a, $b): bool
    {
        return gettype($a) === gettype($b) && 0 === ($a <=> $b);
    }

    /**
     * @param mixed $to
     */
    public function isChangedTo(ResourceInterface $resource, string $property, $to): bool
    {
        $changeSet = $this->getChangeSet($resource, $property);

        return array_key_exists(1, $changeSet) && $this->isEqual($changeSet[1], $to);
    }

    /**
     * @param mixed $from
     * @param mixed $to
     */
    public function isChangedFromTo(ResourceInterface $resource, string $property, $from, $to): bool
    {
        $changeSet = $this->getChangeSet($resource, $property);

        return array_key_exists(0, $changeSet) && $this->isEqual($changeSet[0], $from)
            && array_key_exists(1, $changeSet) && $this->isEqual($changeSet[1], $to);
    }


    /**
     * @inheritDoc
     */
    public function isScheduledForInsert(ResourceInterface $resource): bool
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork(get_class($resource))->isScheduledForInsert($resource);
    }

    /**
     * @inheritDoc
     */
    public function isScheduledForUpdate(ResourceInterface $resource): bool
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork(get_class($resource))->isScheduledForUpdate($resource);
    }

    /**
     * @inheritDoc
     */
    public function isScheduledForRemove(ResourceInterface $resource): bool
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork(get_class($resource))->isScheduledForDelete($resource);
    }

    /**
     * @inheritDoc
     */
    public function persistAndRecompute(ResourceInterface $resource, bool $schedule = false): void
    {
        $manager = $this->getManager(get_class($resource));

        if (!$this->eventQueue->isOpened()) {
            $manager->persist($resource);

            return;
        }

        $uow = $manager->getUnitOfWork();
        if (!($uow->isScheduledForInsert($resource) || $uow->isScheduledForUpdate($resource))) {
            $manager->persist($resource);
        }

        // TODO Remove ? The tracker should build the proper change set without pre-computation.
        $this->tracker->computeChangeSet($resource);

        $metadata = $manager->getClassMetadata(get_class($resource));
        if ($uow->getEntityChangeSet($resource)) {
            $uow->recomputeSingleEntityChangeSet($metadata, $resource);
        } else {
            $uow->computeChangeSet($metadata, $resource);
        }

        if ($schedule) {
            if (null !== $resource->getId()) {
                $this->eventQueue->scheduleUpdate($resource);
            } else {
                $this->eventQueue->scheduleInsert($resource);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(ResourceInterface $resource, bool $schedule = false): void
    {
        $manager = $this->getManager(get_class($resource));

        if (!is_null($resource->getId()) || $this->isScheduledForInsert($resource)) {
            $manager->remove($resource);
        }

        if (!$this->eventQueue->isOpened()) {
            return;
        }

        // TODO Remove ? The tracker should build the proper change set without pre-computation.
        $this->tracker->computeChangeSet($resource);

        if ($schedule) {
            $this->eventQueue->scheduleDelete($resource);
        }
    }

    /**
     * @inheritDoc
     */
    public function scheduleEvent(object $resourceOrEvent, string $eventName): void
    {
        $this->eventQueue->scheduleEvent($resourceOrEvent, $eventName);
    }

    /**
     * @inheritDoc
     *
     * @TODO Make protected
     */
    public function getManager(string $entityClass = null): EntityManagerInterface
    {
        if ($entityClass) {
            // TODO Performance issue ?
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->registry->getManagerForClass($entityClass);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->registry->getManager();
    }

    /**
     * Returns the unit of work.
     *
     * @param string $class
     *
     * @return UnitOfWork
     */
    private function getUnitOfWork(string $class): UnitOfWork
    {
        return $this->getManager($class)->getUnitOfWork();
    }
}

<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

/**
 * Class PersistenceHelper
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceHelper implements PersistenceHelperInterface
{
    /**
     * // TODO use doctrine registry to get resource own manager (if not default)
     * // TODO Retrieve the manager from tracker ?
     *
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var PersistenceTrackerInterface
     */
    protected $tracker;

    /**
     * @var PersistenceEventQueueInterface
     */
    protected $eventQueue;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface         $manager
     * @param PersistenceTrackerInterface    $tracker
     * @param PersistenceEventQueueInterface $eventQueue
     */
    public function __construct(
        EntityManagerInterface $manager,
        PersistenceTrackerInterface $tracker,
        PersistenceEventQueueInterface $eventQueue
    ) {
        $this->manager = $manager;
        $this->tracker = $tracker;
        $this->eventQueue = $eventQueue;
    }

    /**
     * @inheritdoc
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @inheritdoc
     */
    public function getChangeSet(ResourceInterface $resource, $property = null)
    {
        return $this->tracker->getChangeSet($resource, $property);
    }

    /**
     * @inheritdoc
     */
    public function isChanged(ResourceInterface $resource, $properties)
    {
        $changeSet = $this->getChangeSet($resource);

        if (is_string($properties)) {
            return array_key_exists($properties, $changeSet);
        } elseif (is_array($properties)) {
            return !empty(array_intersect($properties, array_keys($changeSet)));
        }

        throw new \InvalidArgumentException('Expected string or array.');
    }

    /**
     * @inheritdoc
     */
    public function isScheduledForInsert(ResourceInterface $resource)
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork()->isScheduledForInsert($resource);
    }

    /**
     * @inheritdoc
     */
    public function isScheduledForUpdate(ResourceInterface $resource)
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork()->isScheduledForUpdate($resource);
    }

    /**
     * @inheritdoc
     */
    public function isScheduledForRemove(ResourceInterface $resource)
    {
        // TODO Check event queue ?
        return $this->getUnitOfWork()->isScheduledForDelete($resource);
    }

    /**
     * @inheritdoc
     */
    public function persistAndRecompute(ResourceInterface $resource, $schedule = false)
    {
        $uow = $this->getUnitOfWork();

        if (!($uow->isScheduledForInsert($resource) || $uow->isScheduledForUpdate($resource))) {
            $this->manager->persist($resource);
        }

        // TODO Remove ? The tracker may build the proper change set without pre-computation.
        $this->tracker->computeChangeSet($resource);

        $metadata = $this->manager->getClassMetadata(get_class($resource));
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
     * @inheritdoc
     */
    public function remove(ResourceInterface $resource, $schedule = false)
    {
        if (null !== $resource->getId()) {
            $this->manager->remove($resource);
        }

        // TODO Remove ? The tracker may build the proper change set without pre-computation.
        $this->tracker->computeChangeSet($resource);

        if ($schedule) {
            $this->eventQueue->scheduleDelete($resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function scheduleEvent($eventName, $resourceOrEvent)
    {
        $this->eventQueue->scheduleEvent($eventName, $resourceOrEvent);
    }

    /**
     * Returns the unit of work.
     *
     * @return \Doctrine\ORM\UnitOfWork
     */
    private function getUnitOfWork()
    {
        return $this->manager->getUnitOfWork();
    }
}

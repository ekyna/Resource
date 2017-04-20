<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Ekyna\Component\Resource\Behavior\BehaviorExecutorInterface;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;

/**
 * Class EntityListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityListener
{
    protected PersistenceEventQueueInterface $eventQueue;
    protected BehaviorExecutorInterface $behaviorExecutor;

    public function __construct(PersistenceEventQueueInterface $eventQueue, BehaviorExecutorInterface $behaviorExecutor)
    {
        $this->eventQueue = $eventQueue;
        $this->behaviorExecutor = $behaviorExecutor;
    }

    /**
     * On flush event handler.
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @see \Doctrine\ORM\UniOfWork::commit
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $this->eventQueue->setOpened(true);

        $uow = $eventArgs->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->behaviorExecutor->execute($entity, Behaviors::INSERT);
                $this->eventQueue->scheduleInsert($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->behaviorExecutor->execute($entity, Behaviors::UPDATE);
                $this->eventQueue->scheduleUpdate($entity);
            }
        }

        // TODO move collections before update ?
        foreach ($uow->getScheduledCollectionDeletions() as $collections) {
            foreach ($collections as $collection) {
                foreach ($collection as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        $this->behaviorExecutor->execute($entity, Behaviors::DELETE);
                        $this->eventQueue->scheduleDelete($entity);
                    }
                }
            }
        }
        foreach ($uow->getScheduledCollectionUpdates() as $collections) {
            foreach ($collections as $collection) {
                foreach ($collection as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        if ($entity->getId()) {
                            $this->behaviorExecutor->execute($entity, Behaviors::UPDATE);
                            $this->eventQueue->scheduleUpdate($entity);
                        } else {
                            $this->behaviorExecutor->execute($entity, Behaviors::INSERT);
                            $this->eventQueue->scheduleInsert($entity);
                        }
                    }
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->behaviorExecutor->execute($entity, Behaviors::DELETE);
                $this->eventQueue->scheduleDelete($entity);
            }
        }

        $this->eventQueue->flush();

        $this->eventQueue->setOpened(false);
    }
}

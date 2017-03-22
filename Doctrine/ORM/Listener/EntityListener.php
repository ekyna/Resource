<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;

/**
 * Class EntityListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityListener implements EventSubscriber
{
    /**
     * @var PersistenceEventQueueInterface
     */
    protected $eventQueue;


    /**
     * Constructor.
     *
     * @param PersistenceEventQueueInterface $eventQueue
     */
    public function __construct(PersistenceEventQueueInterface $eventQueue)
    {
        $this->eventQueue = $eventQueue;
    }

    /**
     * On flush event handler.
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @see \Doctrine\ORM\UniOfWork::commit
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->eventQueue->setOpened(true);

        $uow = $eventArgs->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->eventQueue->scheduleInsert($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->eventQueue->scheduleUpdate($entity);
            }
        }

        // TODO move collections before update ?
        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            foreach ($col as $c) {
                foreach ($c as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        $this->eventQueue->scheduleDelete($entity);
                    }
                }
            }
        }
        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            foreach ($col as $c) {
                foreach ($c as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        if ($entity->getId()) {
                            $this->eventQueue->scheduleUpdate($entity);
                        } else {
                            $this->eventQueue->scheduleInsert($entity);
                        }
                    }
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->eventQueue->scheduleDelete($entity);
            }
        }

        $this->eventQueue->flush();

        $this->eventQueue->setOpened(false);
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }
}

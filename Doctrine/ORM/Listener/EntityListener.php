<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class EntityListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityListener implements EventSubscriber
{
    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var EventQueueInterface
     */
    protected $eventQueue;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry            $registry
     * @param EventQueueInterface $eventQueue
     */
    public function __construct(ConfigurationRegistry $registry, EventQueueInterface $eventQueue)
    {
        $this->registry = $registry;
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

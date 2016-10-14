<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
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
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry            $registry
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(ConfigurationRegistry $registry, ResourceEventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
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
                $this->dispatchInsertEvent($entity);
            }
        }

        // TODO new entities created from there won't receive insert event

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->dispatchUpdateEvent($entity);
            }
        }

        // TODO entities updated from there won't receive update event

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ResourceInterface) {
                $this->dispatchDeleteEvent($entity);
            }
        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            foreach ($col as $c) {
                foreach ($c as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        $this->dispatchDeleteEvent($entity);
                    }
                }
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            foreach ($col as $c) {
                foreach ($c as $entity) {
                    if ($entity instanceof ResourceInterface) {
                        if (null === $entity->getId()) {
                            $this->dispatchInsertEvent($entity);
                        } else {
                            $this->dispatchUpdateEvent($entity);
                        }
                    }
                }
            }
        }
    }

    /**
     * Dispatches the resource insert event.
     *
     * @param ResourceInterface $resource
     */
    private function dispatchInsertEvent(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, 'insert')) {
            $this->dispatchResourceEvent($eventName, $resource);
        }
    }

    /**
     * Dispatches the resource update event.
     *
     * @param \Ekyna\Component\Resource\Model\ResourceInterface $resource
     */
    private function dispatchUpdateEvent(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, 'update')) {
            $this->dispatchResourceEvent($eventName, $resource);
        }
    }

    /**
     * Dispatches the resource delete event.
     *
     * @param ResourceInterface $resource
     */
    private function dispatchDeleteEvent(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, 'delete')) {
            $this->dispatchResourceEvent($eventName, $resource);
        }
    }

    /**
     * Dispatches the resource event.
     *
     * @param string            $eventName
     * @param ResourceInterface $resource
     */
    private function dispatchResourceEvent($eventName, ResourceInterface $resource)
    {
        if (null !== $event = $this->dispatcher->createResourceEvent($resource, false)) {
            $this->dispatcher->dispatch($eventName, $event);
        }
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

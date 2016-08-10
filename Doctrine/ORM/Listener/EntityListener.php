<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Ekyna\Bundle\AdminBundle\Exception\NotFoundConfigurationException;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Component\Resource\Event\PersistenceEvent;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry   $registry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ConfigurationRegistry $registry, EventDispatcherInterface $dispatcher)
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
        $this->manager = $eventArgs->getEntityManager();
        $uow = $this->manager->getUnitOfWork();

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
    public function dispatchInsertEvent(ResourceInterface $resource)
    {
        try {
            $eventName = sprintf('%s.insert', $this->getResourceId($resource));

            $this->dispatcher->dispatch($eventName, $this->createPersistenceEvent($resource));
        } catch(NotFoundConfigurationException $e) {

        }
    }

    /**
     * Dispatches the resource update event.
     *
     * @param \Ekyna\Component\Resource\Model\ResourceInterface $resource
     */
    public function dispatchUpdateEvent(ResourceInterface $resource)
    {
        try {
            $eventName = sprintf('%s.update', $this->getResourceId($resource));

            $this->dispatcher->dispatch($eventName, $this->createPersistenceEvent($resource));
        } catch (NotFoundConfigurationException $e) {

        }
    }

    /**
     * Dispatches the resource delete event.
     *
     * @param ResourceInterface $resource
     */
    public function dispatchDeleteEvent(ResourceInterface $resource)
    {
        try {
            $eventName = sprintf('%s.delete', $this->getResourceId($resource));

            $this->dispatcher->dispatch($eventName, $this->createPersistenceEvent($resource));
        } catch (NotFoundConfigurationException $e) {

        }
    }


    /**
     * Creates the persistence event.
     *
     * @param ResourceInterface $resource
     *
     * @return PersistenceEvent
     */
    private function createPersistenceEvent(ResourceInterface $resource)
    {
        $event = new PersistenceEvent();

        $event->setResource($resource);
        $event->setManager($this->manager);

        return $event;
    }

    /**
     * Returns the resource identifier.
     *
     * @param ResourceInterface $resource
     *
     * @return string
     */
    private function getResourceId(ResourceInterface $resource)
    {
        $configuration = $this->registry->findConfiguration($resource);

        return $configuration->getResourceId();
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

<?php

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Trait ResourceEventDispatcherTrait
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ResourceEventDispatcherTrait
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var EventQueueInterface
     */
    protected $eventQueue;


    /**
     * Sets the configuration registry.
     *
     * @param ConfigurationRegistry $registry
     */
    public function setConfigurationRegistry($registry)
    {
        $this->registry = $registry;
    }

    /**
     * Sets the event queue.
     *
     * @param EventQueueInterface $eventQueue
     */
    public function setEventQueue(EventQueueInterface $eventQueue)
    {
        $this->eventQueue = $eventQueue;
    }

    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param string                                   $eventName
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    public function scheduleEvent($eventName, $resourceOrEvent)
    {
        $this->eventQueue->scheduleEvent($eventName, $resourceOrEvent);
    }

    /**
     * Creates the resource event.
     *
     * @param ResourceInterface $resource
     * @param bool              $throwException
     *
     * @return ResourceEventInterface|null
     */
    public function createResourceEvent(ResourceInterface $resource, $throwException = true)
    {
        if ($config = $this->registry->findConfiguration($resource, $throwException)) {
            $class = $config->getEventClass();

            /** @var \Ekyna\Component\Resource\Event\ResourceEventInterface $event */
            $event = new $class;
            $event->setResource($resource);

            return $event;
        }

        return null;
    }

    /**
     * Returns the resource event name.
     *
     * @param ResourceInterface $resource
     * @param string            $suffix
     *
     * @return string|null
     */
    public function getResourceEventName(ResourceInterface $resource, $suffix)
    {
        if (null !== $configuration = $this->registry->findConfiguration($resource, false)) {
            return sprintf('%s.%s', $configuration->getResourceId(), $suffix);
        }

        return null;
    }
}

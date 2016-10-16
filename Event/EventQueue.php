<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\PersistenceEventException;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class EventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EventQueue implements EventQueueInterface
{
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';


    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $queue;


    /**
     * Constructor.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function scheduleInsert(ResourceInterface $resource)
    {
        $this->scheduleEvent(static::INSERT, $resource);
    }

    /**
     * @inheritdoc
     */
    public function scheduleUpdate(ResourceInterface $resource)
    {
        $this->scheduleEvent(static::UPDATE, $resource);
    }

    /**
     * @inheritdoc
     */
    public function scheduleDelete(ResourceInterface $resource)
    {
        $this->scheduleEvent(static::DELETE, $resource);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        while (!$this->isEmpty()) {
            foreach ($this->getTypes() as $type) {
                foreach ($this->queue[$type] as $key => $resource) {
                    if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, $type)) {
                        $event = $this->dispatcher->createResourceEvent($resource);

                        $this->dispatcher->dispatch($eventName, $event);
                    }

                    unset($this->queue[$type][$key]);
                }
            }
        }

        $this->reset();
    }

    /**
     * Schedules a resource event of the given type.
     *
     * @param string $type
     * @param ResourceInterface $resource
     */
    private function scheduleEvent($type, ResourceInterface $resource)
    {
        $oid = spl_object_hash($resource);

        // Watch for event conflict
        foreach (array_diff($this->getTypes(), [$type]) as $other) {
            if (isset($this->queue[$other][$oid])) {
                throw new PersistenceEventException("Already scheduled for $other.");
            }
        }

        // Queue if not already queued
        if (!isset($this->queue[$type][$oid])) {
            $this->queue[$type][$oid] = $resource;
        }
    }

    /**
     * Resets the event queue.
     */
    private function reset()
    {
        $this->queue = [
            static::INSERT => [],
            static::UPDATE => [],
            static::DELETE => [],
        ];
    }

    /**
     * Returns whether or not the queue is empty.
     *
     * @return bool
     */
    private function isEmpty()
    {
        return empty($this->queue[static::INSERT])
            && empty($this->queue[static::UPDATE])
            && empty($this->queue[static::DELETE]);
    }

    /**
     * Returns the events types.
     *
     * @return array
     */
    private function getTypes()
    {
        return [static::INSERT, static::UPDATE, static::DELETE];
    }
}

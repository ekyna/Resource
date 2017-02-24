<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class EventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EventQueue implements EventQueueInterface
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $opened = false;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry            $registry
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(
        ConfigurationRegistry $registry,
        ResourceEventDispatcherInterface $dispatcher
    ) {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;

        // TODO logger to track queued events

        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function setOpened($opened)
    {
        $this->opened = (bool)$opened;
    }

    /**
     * @inheritdoc
     */
    public function scheduleEvent($eventName, $resourceOrEvent)
    {
        $this->enqueue($eventName, $resourceOrEvent);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        while (!empty($queue = $this->clear())) {
            $queue = $this->sortQueue($queue);

            foreach ($queue as $eventName => $resources) {
                foreach ($resources as $oid => $resourceOrEvent) {
                    if (!$resourceOrEvent instanceof ResourceEventInterface) {
                        $resourceOrEvent = $this->dispatcher->createResourceEvent($resourceOrEvent);
                    }

                    $this->dispatcher->dispatch($eventName, $resourceOrEvent);
                }
            }
        }
    }

    /**
     * Returns whether the queue is opened or not.
     *
     * @return boolean
     */
    protected function isOpened()
    {
        return $this->opened;
    }

    /**
     * Schedules a resource event of the given type.
     *
     * @param string                                   $eventName
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    protected function enqueue($eventName, $resourceOrEvent)
    {
        if (!$this->isOpened()) {
            throw new RuntimeException("The event queue is closed.");
        }

        if (!preg_match('~^[a-z_]+\.[a-z_]+\.[a-z_]+$~', $eventName)) {
            throw new InvalidArgumentException("Unexpected event name '{$eventName}'.");
        }

        if ($resourceOrEvent instanceof ResourceInterface) {
            $resource = $resourceOrEvent;
        } elseif ($resourceOrEvent instanceof ResourceEventInterface) {
            $resource = $resourceOrEvent->getResource();
        } else {
            throw new InvalidArgumentException("Expected instanceof ResourceInterface or ResourceEventInterface");
        }

        $oid = spl_object_hash($resource);

        // TODO we are enqueueing into en empty queue (cleared by the flush method)
        //      so duplication and conflict can't be resolved
        // TODO see persistAndRecompute (.., $andSchedule = false)

        // Don't add twice
        if (isset($this->queue[$eventName]) && isset($this->queue[$eventName][$oid])) {
            return;
        }

        $this->preventEventConflict($eventName, $oid);

        if (!isset($this->queue[$eventName])) {
            $this->queue[$eventName] = [];
        }

        $this->queue[$eventName][$oid] = $resourceOrEvent;
    }

    /**
     * Sorts the event queue by resource hierarchy and event priority.
     *
     * @param array $queue
     *
     * @return array The sorted queue.
     */
    protected function sortQueue(array $queue)
    {
        if (!@uksort($queue, $this->getQueueSortingCallback())) {
            throw new RuntimeException("Failed to sort the event queue.");
        }

        return $queue;
    }

    /**
     * Throws an exception on en event conflict case.
     *
     * @param string $eventName
     * @param string $oid
     *
     * @throws \Ekyna\Component\Resource\Exception\ResourceExceptionInterface
     */
    protected function preventEventConflict($eventName, $oid)
    {

    }

    /**
     * Returns the queue sorting callback.
     *
     * @return \Closure
     */
    protected function getQueueSortingCallback()
    {
        return function ($a, $b) {
            // By resource hierarchy
            $aId = $this->getEventPrefix($a);
            $bId = $this->getEventPrefix($b);

            // [$resourceId => $parentId]
            $parentMap = $this->registry->getParentMap();

            if (isset($parentMap[$aId]) && $parentMap[$aId] == $bId) {
                // B is a parent of A
                return -1;
            } elseif (isset($parentMap[$bId]) && $parentMap[$bId] == $aId) {
                // A is a parent of B
                return 1;
            }

            // By suffix priority
            $aPriority = $this->getEventPriority($a);
            $bPriority = $this->getEventPriority($b);

            if ($aPriority < $bPriority) {
                return -1;
            } elseif ($bPriority < $aPriority) {
                return 1;
            }

            return 0;
        };
    }

    /**
     * Returns the event priority.
     *
     * @param string $eventName
     *
     * @return int
     */
    protected function getEventPriority($eventName)
    {
        // TODO We could use the resource configuration to get the custom event's priority

        return 0;
    }

    /**
     * Returns the event prefix (resource id).
     *
     * @param string $eventName
     *
     * @return string
     */
    protected function getEventPrefix($eventName)
    {
        return substr($eventName, 0, strrpos($eventName, '.'));
    }

    /**
     * Returns the event suffix (action).
     *
     * @param string $eventName
     *
     * @return string
     */
    protected function getEventSuffix($eventName)
    {
        return substr($eventName, strrpos($eventName, '.') + 1);
    }

    /**
     * Clears the event queue.
     *
     * @return array The copy of the event queue
     */
    protected function clear()
    {
        $queue = $this->queue;

        $this->queue = [];

        return $queue;
    }
}

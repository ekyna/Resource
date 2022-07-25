<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

use function spl_object_hash;
use function uksort;

/**
 * Class EventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EventQueue implements EventQueueInterface
{
    /** @var array<string, array<string, ResourceEventInterface>> */
    protected array $queue  = [];
    protected bool  $opened = false;

    public function __construct(
        protected readonly ResourceRegistryInterface        $registry,
        protected readonly ResourceEventDispatcherInterface $dispatcher
    ) {
        // TODO logger to track queued events
    }

    public function setOpened(bool $opened): void
    {
        if ($opened === $this->opened) {
            return;
        }

        if ($this->opened = $opened) {
            $this->dispatcher->dispatch(new Event(), QueueEvents::QUEUE_OPEN);
        } else {
            $this->dispatcher->dispatch(new Event(), QueueEvents::QUEUE_CLOSE);
        }
    }

    public function isOpened(): bool
    {
        return $this->opened;
    }

    public function scheduleEvent(ResourceInterface|ResourceEventInterface $resourceOrEvent, string $eventName): void
    {
        $this->enqueue($resourceOrEvent, $eventName);
    }

    public function clearEvent(ResourceInterface $resource, string $eventName): void
    {
        if (!isset($this->queue[$eventName])) {
            return;
        }

        $oid = spl_object_hash($resource);

        unset($this->queue[$eventName][$oid]);
    }

    public function flush(): void
    {
        $this->dispatcher->dispatch(new Event(), QueueEvents::QUEUE_FLUSH);

        while (!empty($queue = $this->clear())) {
            foreach ($queue as $eventName => $resources) {
                foreach ($resources as $event) {
                    $this->dispatcher->dispatch($event, $eventName);
                }
            }
        }
    }

    /**
     * Schedules a resource event of the given type.
     */
    protected function enqueue(ResourceInterface|ResourceEventInterface $resourceOrEvent, string $eventName): void
    {
        if (!$this->isOpened()) {
            throw new RuntimeException('The event queue is closed.');
        }

        if (!preg_match('~^[a-z_]+\.[a-z_]+\.[a-z_]+$~', $eventName)) {
            throw new InvalidArgumentException("Unexpected event name '$eventName'.");
        }

        if ($resourceOrEvent instanceof ResourceInterface) {
            $resourceOrEvent = $this->dispatcher->createResourceEvent($resource = $resourceOrEvent);
        } else {
            $resource = $resourceOrEvent->getResource();
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
     * @return array The sorted queue.
     */
    protected function sortQueue(array $queue): array
    {
        if (!uksort($queue, $this->getQueueSortingCallback())) {
            throw new RuntimeException('Failed to sort the event queue.');
        }

        return $queue;
    }

    /**
     * Throws an exception on en event conflict case.
     */
    protected function preventEventConflict(string $eventName, string $oid): void
    {
    }

    /**
     * Returns the queue sorting callback.
     */
    protected function getQueueSortingCallback(): callable
    {
        // [$resourceId => $parentId]
        $parentMap = $this->registry->getParentMap();

        // [$resourceId => $parentId]
        $depthMap = $this->registry->getDepthMap();

        // [$resourceId => $priority]
        $priorityMap = $this->registry->getEventPriorityMap();

        /**
         * Returns whether $a is a child of $b.
         *
         * @param string $a
         * @param string $b
         *
         * @return bool
         *
         * @todo Move in resource registry
         */
        $isChildOf = function (string $a, string $b) use ($parentMap) {
            while (isset($parentMap[$a])) {
                $parentId = $parentMap[$a];
                if ($parentId === $b) {
                    return true;
                }
                $a = $parentId;
            }

            return false;
        };

        return function ($a, $b) use ($isChildOf, $depthMap, $priorityMap) {
            $aId = $this->getEventPrefix($a);
            $bId = $this->getEventPrefix($b);

            // By prefix (resource id) priority
            $aPriority = $priorityMap[$aId] ?? 0;
            $bPriority = $priorityMap[$bId] ?? 0;

            if ($aPriority > $bPriority) {
                return -1;
            } elseif ($bPriority > $aPriority) {
                return 1;
            }

            // By resource hierarchy (children first)
            if ($isChildOf($aId, $bId)) {
                // B is a parent of A
                return 1;
            } elseif ($isChildOf($bId, $aId)) {
                // A is a parent of B
                return -1;
            }

            // By resource depth (children first)
            $aDepth = $depthMap[$aId] ?? 0;
            $bDepth = $depthMap[$bId] ?? 0;

            if ($aDepth > $bDepth) {
                return 1;
            } elseif ($bDepth > $aDepth) {
                return -1;
            }

            // By suffix priority
            $aPriority = $this->getEventPriority($a);
            $bPriority = $this->getEventPriority($b);

            if ($aPriority > $bPriority) {
                return -1;
            } elseif ($bPriority > $aPriority) {
                return 1;
            }

            return $aPriority <=> $bPriority;
        };
    }

    /**
     * Returns the event priority.
     *
     * @param string $eventName
     *
     * @return int
     * @deprecated
     */
    protected function getEventPriority(string $eventName): int
    {
        // TODO We could use the resource configuration to get the custom event's priority

        return 0;
    }

    /**
     * Returns the event prefix (ie the resource id).
     */
    protected function getEventPrefix(string $eventName): string
    {
        return substr($eventName, 0, strrpos($eventName, '.'));
    }

    /**
     * Returns the event suffix (action).
     */
    protected function getEventSuffix(string $eventName): string
    {
        return substr($eventName, strrpos($eventName, '.') + 1);
    }

    /**
     * Clears the event queue.
     *
     * @return array<string, array<string, ResourceEventInterface>> The copy of the event queue
     */
    protected function clear(): array
    {
        $queue = $this->queue;

        $this->queue = [];

        $queue = $this->sortQueue($queue);

        $this->dispatcher->dispatch(new Event(), QueueEvents::QUEUE_CLEAR);

        return $queue;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\EventQueue;
use Ekyna\Component\Resource\Exception\PersistenceEventException;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function in_array;

/**
 * Class PersistenceEventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PersistenceEventQueue extends EventQueue implements PersistenceEventQueueInterface
{
    public function __construct(
        ResourceRegistryInterface                      $registry,
        ResourceEventDispatcherInterface               $dispatcher,
        protected readonly PersistenceTrackerInterface $tracker
    ) {
        parent::__construct($registry, $dispatcher);
    }

    protected function clear(): array
    {
        $this->tracker->clearChangeSets(); // TODO Listen to Ekyna\Component\Resource\Event\QueueEvents::QUEUE_CLEAR event

        return parent::clear();
    }

    public function scheduleInsert(ResourceInterface $resource): void
    {
        $eventName = $this->dispatcher->getResourceEventName($resource, PersistenceEventQueueInterface::INSERT);

        if (null === $eventName) {
            return;
        }

        $this->enqueue($resource, $eventName);
    }

    public function scheduleUpdate(ResourceInterface $resource): void
    {
        $eventName = $this->dispatcher->getResourceEventName($resource, PersistenceEventQueueInterface::UPDATE);

        if (null === $eventName) {
            return;
        }

        $this->enqueue($resource, $eventName);
    }

    public function scheduleDelete(ResourceInterface $resource): void
    {
        $eventName = $this->dispatcher->getResourceEventName($resource, PersistenceEventQueueInterface::DELETE);

        if (null === $eventName) {
            return;
        }

        $this->enqueue($resource, $eventName);
    }

    public function clearEvent(ResourceInterface $resource, string $eventName): void
    {
        if (in_array($eventName, $this->getPersistenceSuffixes(), true)) {
            $eventName = $this->dispatcher->getResourceEventName($resource, $eventName);
        }

        parent::clearEvent($resource, $eventName);
    }

    protected function preventEventConflict(string $eventName, string $oid): void
    {
        parent::preventEventConflict($eventName, $oid);

        // Skip non persistence suffix
        $suffix = $this->getEventSuffix($eventName);
        if (!in_array($suffix, $this->getPersistenceSuffixes(), true)) {
            return;
        }

        // Watch for persistence event conflict
        $prefix = $this->getEventPrefix($eventName);
        foreach (array_diff($this->getPersistenceSuffixes(), [$suffix]) as $other) {
            if (!isset($this->queue[$prefix . '.' . $other])) {
                continue;
            }

            if (!isset($this->queue[$prefix . '.' . $other][$oid])) {
                continue;
            }

            throw new PersistenceEventException("Already scheduled for action '$other'.");
        }
    }

    /**
     * Returns the event priority.
     */
    protected function getEventPriority(string $eventName): int
    {
        $suffix = $this->getEventSuffix($eventName);

        if ($suffix === PersistenceEventQueueInterface::UPDATE) {
            return 9999;
        }

        if ($suffix === PersistenceEventQueueInterface::INSERT) {
            return 9998;
        }

        if ($suffix === PersistenceEventQueueInterface::DELETE) {
            return 9997;
        }

        return parent::getEventPriority($eventName);
    }

    /**
     * Returns the persistence events suffixes.
     */
    private function getPersistenceSuffixes(): array
    {
        return [
            PersistenceEventQueueInterface::INSERT,
            PersistenceEventQueueInterface::UPDATE,
            PersistenceEventQueueInterface::DELETE,
        ];
    }
}

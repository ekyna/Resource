<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\EventQueue;
use Ekyna\Component\Resource\Exception\PersistenceEventException;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PersistenceEventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PersistenceEventQueue extends EventQueue implements PersistenceEventQueueInterface
{
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    protected PersistenceTrackerInterface $tracker;

    public function __construct(
        ResourceRegistryInterface $registry,
        ResourceEventDispatcherInterface $dispatcher,
        PersistenceTrackerInterface $tracker
    ) {
        $this->tracker = $tracker;

        parent::__construct($registry, $dispatcher);
    }

    protected function clear(): array
    {
        $this->tracker->clearChangeSets();

        return parent::clear();
    }

    public function scheduleInsert(ResourceInterface $resource): void
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, self::INSERT)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->enqueue($resource, $eventName);
        }
    }

    public function scheduleUpdate(ResourceInterface $resource): void
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, self::UPDATE)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->enqueue($resource, $eventName);
        }
    }

    public function scheduleDelete(ResourceInterface $resource): void
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, self::DELETE)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->enqueue($resource, $eventName);
        }
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
            if (isset($this->queue[$prefix . '.' . $other]) && isset($this->queue[$prefix . '.' . $other][$oid])) {
                throw new PersistenceEventException("Already scheduled for action '$other'.");
            }
        }
    }

    /**
     * Returns the event priority.
     */
    protected function getEventPriority(string $eventName): int
    {
        $suffix = $this->getEventSuffix($eventName);

        if ($suffix === self::UPDATE) {
            return 9999;
        } elseif ($suffix === self::INSERT) {
            return 9998;
        } elseif ($suffix === self::DELETE) {
            return 9997;
        }

        return parent::getEventPriority($eventName);
    }

    /**
     * Returns the persistence events suffixes.
     */
    private function getPersistenceSuffixes(): array
    {
        return [self::INSERT, self::UPDATE, self::DELETE];
    }
}

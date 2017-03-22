<?php

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\EventQueue;
use Ekyna\Component\Resource\Exception\PersistenceEventException;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PersistenceEventQueue
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceEventQueue extends EventQueue implements PersistenceEventQueueInterface
{
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';


    /**
     * @var PersistenceTrackerInterface
     */
    protected $tracker;


    /**
     * @inheritDoc
     */
    public function __construct(
        ConfigurationRegistry $registry,
        ResourceEventDispatcherInterface $dispatcher,
        PersistenceTrackerInterface $tracker
    ) {
        $this->tracker = $tracker;

        parent::__construct($registry, $dispatcher);
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        parent::flush();

        $this->tracker->clear();
    }

    /**
     * @inheritDoc
     */
    protected function clear()
    {
        $this->tracker->clearChangeSets();

        return parent::clear();
    }

    /**
     * @inheritdoc
     */
    public function scheduleInsert(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, static::INSERT)) {
            $this->enqueue($eventName, $resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function scheduleUpdate(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, static::UPDATE)) {
            $this->enqueue($eventName, $resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function scheduleDelete(ResourceInterface $resource)
    {
        if (null !== $eventName = $this->dispatcher->getResourceEventName($resource, static::DELETE)) {
            $this->enqueue($eventName, $resource);
        }
    }

    /**
     * @inheritdoc
     */
    protected function preventEventConflict($eventName, $oid)
    {
        parent::preventEventConflict($eventName, $oid);

        // Watch for persistence event conflict
        $prefix = $this->getEventPrefix($eventName);
        $suffix = $this->getEventSuffix($eventName);
        foreach (array_diff($this->getPersistenceSuffixes(), [$suffix]) as $other) {
            if (isset($this->queue[$prefix . '.' . $other]) && isset($this->queue[$prefix . '.' . $other][$oid])) {
                throw new PersistenceEventException("Already scheduled for action '$other'.");
            }
        }
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
        $suffix = $this->getEventSuffix($eventName);

        if ($suffix === static::UPDATE) {
            return 9999;
        } elseif ($suffix === static::INSERT) {
            return 9998;
        } elseif ($suffix === static::DELETE) {
            return 9997;
        }

        return parent::getEventPriority($eventName);
    }

    /**
     * Returns the persistence events suffixes.
     *
     * @return array
     */
    private function getPersistenceSuffixes()
    {
        return [static::INSERT, static::UPDATE, static::DELETE];
    }
}

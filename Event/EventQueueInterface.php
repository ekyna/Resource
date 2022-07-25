<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface EventQueueInterface
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface EventQueueInterface
{
    /**
     * Sets whether the queue is opened.
     *
     * @param bool $opened
     */
    public function setOpened(bool $opened): void;

    /**
     * Returns whether the queue is opened.
     *
     * @return bool
     */
    public function isOpened(): bool;

    /**
     * Schedules the resource event.
     *
     * @throws ResourceExceptionInterface
     */
    public function scheduleEvent(ResourceInterface|ResourceEventInterface $resourceOrEvent, string $eventName): void;

    /**
     * Clears the resource event.
     */
    public function clearEvent(ResourceInterface $resource, string $eventName): void;

    /**
     * Flushes the event queue.
     */
    public function flush(): void;
}

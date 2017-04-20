<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ResourceEventDispatcherInterface
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws ResourceExceptionInterface
     */
    public function scheduleEvent(object $resourceOrEvent, string $eventName): void;

    /**
     * Creates the resource event.
     */
    public function createResourceEvent(
        ResourceInterface $resource,
        bool $throwException = true
    ): ?ResourceEventInterface;

    /**
     * Returns the resource event name.
     */
    public function getResourceEventName(ResourceInterface $resource, string $suffix): ?string;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;

use function is_null;

/**
 * Trait ResourceEventDispatcherTrait
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ResourceEventDispatcherTrait
{
    protected ResourceRegistryInterface $registry;
    protected EventQueueInterface       $eventQueue;

    public function setConfigurationRegistry(ResourceRegistryInterface $registry): void
    {
        $this->registry = $registry;
    }

    public function setEventQueue(EventQueueInterface $eventQueue): void
    {
        $this->eventQueue = $eventQueue;
    }

    /**
     * Schedule the resource event to be dispatched during the persistence phase (onFlush).
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @throws ResourceExceptionInterface
     */
    public function scheduleEvent(object $resourceOrEvent, string $eventName): void
    {
        $this->eventQueue->scheduleEvent($resourceOrEvent, $eventName);
    }

    /**
     * Creates the resource event.
     */
    public function createResourceEvent(
        ResourceInterface $resource,
        bool $throwException = true
    ): ?ResourceEventInterface {
        if ($resource instanceof TranslationInterface) {
            $config = $this->registry->findByTranslation($resource, $throwException);
        } else {
            $config = $this->registry->find($resource, $throwException);
        }

        if (is_null($config)) {
            return null;
        }

        $class = $config->getEventClass();

        /** @var ResourceEventInterface $event */
        $event = new $class();
        $event->setResource($resource);

        return $event;
    }

    /**
     * Returns the resource event name.
     */
    public function getResourceEventName(ResourceInterface $resource, string $suffix): ?string
    {
        if ($resource instanceof TranslationInterface) {
            $config = $this->registry->findByTranslation($resource, false);
            $translation = true;
        } else {
            $config = $this->registry->find($resource);
            $translation = false;
        }

        if (is_null($config)) {
            return null;
        }

        return $config->getEventName($suffix, $translation);
    }
}

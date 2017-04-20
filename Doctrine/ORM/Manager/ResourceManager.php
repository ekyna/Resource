<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Manager;

use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceEvents;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use PDOException;

use function sprintf;

/**
 * Class ResourceManager
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceManager implements ResourceManagerInterface
{
    protected EntityManagerInterface           $wrapped;
    protected ResourceEventDispatcherInterface $dispatcher;
    protected string                           $resourceClass;
    protected string                           $eventPrefix;
    protected bool                             $debug;

    public function configure(string $resourceClass, string $eventPrefix, bool $debug): void
    {
        $this->resourceClass = $resourceClass;
        $this->eventPrefix = $eventPrefix;
        $this->debug = $debug;
    }

    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function setWrapped(EntityManagerInterface $wrapped): void
    {
        $this->wrapped = $wrapped;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): ClassMetadata
    {
        return $this->wrapped->getClassMetadata($this->resourceClass);
    }

    public function persist(ResourceInterface $resource): void
    {
        $this->supports($resource);

        $this->wrapped->persist($resource);
    }

    public function refresh(ResourceInterface $resource): void
    {
        $this->supports($resource);

        $this->wrapped->refresh($resource);
    }

    public function remove(ResourceInterface $resource): void
    {
        $this->supports($resource);

        $this->wrapped->remove($resource);
    }

    public function flush(): void
    {
        $this->wrapped->flush();
    }

    public function clear(): void
    {
        $this->wrapped->clear($this->resourceClass);
    }

    /**
     * @inheritDoc
     */
    public function save($resourceOrEvent): ResourceEventInterface
    {
        $resource = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent->getResource()
            : $resourceOrEvent;

        if (0 < $resource->getId()) {
            return $this->update($resourceOrEvent);
        }

        return $this->create($resourceOrEvent);
    }

    /**
     * @inheritDoc
     */
    public function create($resourceOrEvent): ResourceEventInterface
    {
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

        $this->supports($event->getResource());

        if (0 < $event->getResource()->getId()) {
            throw new LogicException('Please use the update() method.');
        }

        try {
            $this->dispatchResourceEvent(ResourceEvents::PRE_CREATE, $event);

            if (!$event->isPropagationStopped()) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->persistResource($event);

                if (!$event->isPropagationStopped()) {
                    $this->dispatchResourceEvent(ResourceEvents::POST_CREATE, $event);
                }
            }
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ResourceExceptionInterface $exception) {
            if ($this->debug) {
                throw $exception;
            }

            $event->addMessage(ResourceMessage::create($exception->getMessage(), ResourceMessage::TYPE_ERROR));
        }

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function update($resourceOrEvent, $hard = false): ResourceEventInterface
    {
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

        $this->supports($event->getResource());

        $event->setHard($event->getHard() || $hard);

        try {
            $this->dispatchResourceEvent(ResourceEvents::PRE_UPDATE, $event);

            if (!$event->isPropagationStopped()) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->persistResource($event);

                if (!$event->isPropagationStopped()) {
                    $this->dispatchResourceEvent(ResourceEvents::POST_UPDATE, $event);
                }
            }
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ResourceExceptionInterface $exception) {
            if ($this->debug) {
                throw $exception;
            }

            $event->addMessage(ResourceMessage::create($exception->getMessage(), ResourceMessage::TYPE_ERROR));
        }

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function delete($resourceOrEvent, $hard = false): ResourceEventInterface
    {
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

        $this->supports($event->getResource());

        $event->setHard($event->getHard() || $hard);

        try {
            $this->dispatchResourceEvent(ResourceEvents::PRE_DELETE, $event);

            if (!$event->isPropagationStopped()) {
                $filters = $this->wrapped->getFilters();
                $eventManager = $this->wrapped->getEventManager();
                $disabledListeners = [];

                if ($event->getHard()) {
                    if ($filters->has('softdeleteable')) {
                        $filters->disable('softdeleteable');
                    }

                    foreach ($eventManager->getListeners() as $eventName => $listeners) {
                        foreach ($listeners as $listener) {
                            if ($listener instanceof SoftDeleteableListener) {
                                $eventManager->removeEventListener($eventName, $listener);
                                $disabledListeners[$eventName] = $listener;
                            }
                        }
                    }
                }

                /** @noinspection PhpUnhandledExceptionInspection */
                $this->removeResource($event);

                if ($event->getHard()) {
                    if (!empty($disabledListeners)) {
                        foreach ($disabledListeners as $eventName => $listener) {
                            $eventManager->addEventListener($eventName, $listener);
                        }
                    }

                    if ($filters->has('softdeleteable')) {
                        $filters->enable('softdeleteable');
                    }
                }

                if (!$event->isPropagationStopped()) {
                    $this->dispatchResourceEvent(ResourceEvents::POST_DELETE, $event);
                }
            }
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ResourceExceptionInterface $exception) {
            if ($this->debug) {
                throw $exception;
            }

            $event->addMessage(ResourceMessage::create($exception->getMessage(), ResourceMessage::TYPE_ERROR));
        }

        return $event;
    }

    public function createResourceEvent(ResourceInterface $resource): ResourceEventInterface
    {
        return $this->dispatcher->createResourceEvent($resource);
    }

    public function getClassName(): string
    {
        return $this->resourceClass;
    }

    /**
     * Dispatches the resource event.
     *
     * @param string                                   $name
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @return ResourceEventInterface
     */
    protected function dispatchResourceEvent(string $name, $resourceOrEvent): ResourceEventInterface
    {
        ResourceEvents::isValid($name);

        $name = $this->getEventName($name);

        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->dispatcher->dispatch($event, $name);
    }

    protected function persistResource(ResourceEventInterface $event): void
    {
        $resource = $event->getResource();

        try {
            $this->wrapped->persist($resource);
            $this->wrapped->flush();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ResourceExceptionInterface $exception) {
            $event->addMessage(ResourceMessage::create(
                $exception->getMessage() ?? 'An error occurred',
                ResourceMessage::TYPE_ERROR
            ));

            return;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (DoctrineException $exception) {
            if ($this->debug) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $exception;
            }

            $event->addMessage(
                ResourceMessage::create(
                    'message.persist.failure',
                    ResourceMessage::TYPE_ERROR
                )->setDomain('EkynaResource')
            );

            return;
        }

        $event->addMessage(
            ResourceMessage::create(
                'message.persist.success',
                ResourceMessage::TYPE_SUCCESS
            )->setDomain('EkynaResource')
        );
    }

    protected function removeResource(ResourceEventInterface $event): void
    {
        $resource = $event->getResource();

        try {
            $this->wrapped->remove($resource);
            $this->wrapped->flush();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ResourceExceptionInterface $exception) {
            $event->addMessage(ResourceMessage::create(
                $exception->getMessage() ?? 'An error occurred',
                ResourceMessage::TYPE_ERROR
            ));

            return;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (DoctrineException $exception) {
            if ($this->debug) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $exception;
            }

            if (null !== $previous = $exception->getPrevious()) {
                if ($previous instanceof PDOException && $previous->getCode() === 23000) {
                    $event->addMessage(
                        ResourceMessage::create(
                            'message.remove.integrity',
                            ResourceMessage::TYPE_ERROR
                        )->setDomain('EkynaResource')
                    );

                    return;
                }
            }

            $event->addMessage(
                ResourceMessage::create(
                    'message.remove.failure',
                    ResourceMessage::TYPE_ERROR
                )->setDomain('EkynaResource')
            );

            return;
        }

        $event->addMessage(
            ResourceMessage::create(
                'message.remove.success',
                ResourceMessage::TYPE_SUCCESS
            )->setDomain('EkynaResource')
        );
    }

    /**
     * Returns the resource event name for the given action.
     */
    protected function getEventName($action): string
    {
        return sprintf('%s.%s', $this->eventPrefix, $action);
    }

    /**
     * Throws exception if unexpected resource.
     */
    private function supports(ResourceInterface $resource): void
    {
        if ($resource instanceof $this->resourceClass) {
            return;
        }

        throw new UnexpectedTypeException($resource, $this->resourceClass);
    }
}

<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Operator;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Component\Resource\Configuration\ConfigurationInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ResourceManager
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Swap with ResourceManagerDecorator when ready.
 */
class ResourceOperator implements ResourceOperatorInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @var bool
     */
    protected $debug;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface   $manager
     * @param EventDispatcherInterface $dispatcher
     * @param ConfigurationInterface   $config
     * @param bool                     $debug
     */
    public function __construct(
        EntityManagerInterface $manager,
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        $debug = false
    ) {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($resourceOrEvent)
    {
        $resource = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent->getResource() : $resourceOrEvent;
        if (0 < $resource->getId()) {
            return $this->update($resourceOrEvent);
        }

        return $this->create($resourceOrEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function detach($resource)
    {
        $this->manager->detach($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function merge($resource)
    {
        $this->manager->merge($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh($resource)
    {
        $this->manager->refresh($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->manager->clear($this->config->getResourceClass());
    }

    /**
     * {@inheritdoc}
     */
    public function create($resourceOrEvent)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $this->dispatcher->dispatch($this->config->getEventName('pre_create'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_create'), $event);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function update($resourceOrEvent)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $this->dispatcher->dispatch($this->config->getEventName('pre_update'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_update'), $event);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($resourceOrEvent, $hard = false)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $event->setHard($event->getHard() || $hard);
        $this->dispatcher->dispatch($this->config->getEventName('pre_delete'), $event);

        if (!$event->isPropagationStopped()) {
            $eventManager = $this->manager->getEventManager();
            $disabledListeners = [];
            if ($event->getHard()) {
                foreach ($eventManager->getListeners() as $eventName => $listeners) {
                    foreach ($listeners as $listener) {
                        if ($listener instanceof SoftDeleteableListener) {
                            $eventManager->removeEventListener($eventName, $listener);
                            $disabledListeners[$eventName] = $listener;
                        }
                    }
                }
            }

            $this->removeResource($event);

            if (!empty($disabledListeners)) {
                foreach ($disabledListeners as $eventName => $listener) {
                    $eventManager->addEventListener($eventName, $listener);
                }
            }

            $this->dispatcher->dispatch($this->config->getEventName('post_delete'), $event);
        }

        return $event;
    }

    /**
     * Persists a resource.
     *
     * @param ResourceEvent $event
     *
     * @return ResourceEvent
     * @throws DBALException
     * @throws \Exception
     */
    protected function persistResource(ResourceEvent $event)
    {
        $resource = $event->getResource();

        // TODO Validation ?

        try {
            $this->manager->persist($resource);
            $this->manager->flush();
        } catch (DBALException $e) {
            if ($this->debug) {
                throw $e;
            }
            $event->addMessage(new ResourceMessage(
                'ekyna_admin.resource.message.persist.failure',
                ResourceMessage::TYPE_ERROR
            ));

            return $event;
        }

        return $event->addMessage(new ResourceMessage(
            'ekyna_admin.resource.message.persist.success',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Removes a resource.
     *
     * @param ResourceEvent $event
     *
     * @return ResourceEvent
     * @throws DBALException
     * @throws \Exception
     */
    protected function removeResource(ResourceEvent $event)
    {
        $resource = $event->getResource();

        try {
            $this->manager->remove($resource);
            $this->manager->flush();
        } catch (DBALException $e) {
            if ($this->debug) {
                throw $e;
            }
            if (null !== $previous = $e->getPrevious()) {
                if ($previous instanceof \PDOException && $previous->getCode() == 23000) {
                    return $event->addMessage(new ResourceMessage(
                        'ekyna_admin.resource.message.remove.integrity',
                        ResourceMessage::TYPE_ERROR
                    ));
                }
            }

            return $event->addMessage(new ResourceMessage(
                'ekyna_admin.resource.message.remove.failure',
                ResourceMessage::TYPE_ERROR
            ));
        }

        return $event->addMessage(new ResourceMessage(
            'ekyna_admin.resource.message.remove.success',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Creates the resource event.
     *
     * @param object $resource
     *
     * @return ResourceEvent
     */
    protected function createResourceEvent($resource)
    {
        if (null !== $eventClass = $this->config->getEventClass()) {
            $event = new $eventClass($resource);
        } else {
            $event = new ResourceEvent();
            $event->setResource($resource);
        }

        return $event;
    }
}

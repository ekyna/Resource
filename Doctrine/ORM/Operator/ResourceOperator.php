<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Operator;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Configuration\ConfigurationInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Gedmo\SoftDeleteable\SoftDeleteableListener;

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
     * @var ResourceEventDispatcherInterface
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
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param ConfigurationInterface   $config
     * @param bool                     $debug
     */
    public function __construct(
        EntityManagerInterface $manager,
        ResourceEventDispatcherInterface $dispatcher,
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
        $resource = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent->getResource()
            : $resourceOrEvent;

        if (0 < $resource->getId()) {
            return $this->update($resourceOrEvent);
        }

        return $this->create($resourceOrEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function detach(ResourceInterface $resource)
    {
        $this->manager->detach($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ResourceInterface$resource)
    {
        $this->manager->merge($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(ResourceInterface $resource)
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
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

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
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

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
        $event = $resourceOrEvent instanceof ResourceEventInterface
            ? $resourceOrEvent
            : $this->createResourceEvent($resourceOrEvent);

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
     * @inheritdoc
     */
    public function createResourceEvent($resource)
    {
        return $this->dispatcher->createResourceEvent($resource);
    }

    /**
     * Persists a resource.
     *
     * @param ResourceEventInterface $event
     *
     * @throws DBALException
     * @throws \Exception
     */
    protected function persistResource(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

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

            return;
        }

        $event->addMessage(new ResourceMessage(
            'ekyna_admin.resource.message.persist.success',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Removes a resource.
     *
     * @param ResourceEventInterface $event
     *
     * @throws DBALException
     * @throws \Exception
     */
    protected function removeResource(ResourceEventInterface $event)
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
                    $event->addMessage(new ResourceMessage(
                        'ekyna_admin.resource.message.remove.integrity',
                        ResourceMessage::TYPE_ERROR
                    ));
                    return;
                }
            }

            $event->addMessage(new ResourceMessage(
                'ekyna_admin.resource.message.remove.failure',
                ResourceMessage::TYPE_ERROR
            ));
        }

        $event->addMessage(new ResourceMessage(
            'ekyna_admin.resource.message.remove.success',
            ResourceMessage::TYPE_SUCCESS
        ));
    }
}

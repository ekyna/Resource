<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Component\Resource\Configuration\ConfigurationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ResourceManager
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Use when doctrine bundle will be ready to register.
 */
class ResourceManager extends EntityManagerDecorator
{
    /**
     * @var ConfigurationInterface
     */
    private $config;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $wrapped
     * @param EventDispatcherInterface $dispatcher
     * @param ConfigurationInterface $config
     */
    public function __construct(
        EntityManagerInterface $wrapped,
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config
    ) {
        $this->dispatcher = $dispatcher;
        $this->config = $config;

        parent::__construct($wrapped);
    }

    /**
     * Creates the resource.
     *
     * @param object $resource
     *
     * @return ResourceEvent
     */
    public function create($resource)
    {
        $event = $this->createResourceEvent($resource);
        $this->dispatcher->dispatch($this->config->getEventName('pre_create'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_create'), $event);
        }

        return $event;
    }

    /**
     * Updates the resource.
     *
     * @param object $resource
     *
     * @return ResourceEvent
     */
    public function update($resource)
    {
        $event = $this->createResourceEvent($resource);
        $this->dispatcher->dispatch($this->config->getEventName('pre_update'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_update'), $event);
        }

        return $event;
    }

    /**
     * Deletes the resource.
     *
     * @param object $resource
     *
     * @return ResourceEvent
     */
    public function delete($resource)
    {
        $event = $this->createResourceEvent($resource);
        $this->dispatcher->dispatch($this->config->getEventName('pre_delete'), $event);

        if (!$event->isPropagationStopped()) {
            $this->removeResource($event);

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
     */
    private function persistResource(ResourceEvent $event)
    {
        $resource = $event->getResource();
        $this->persist($resource);

        try {
            $this->flush();
        } catch(DBALException $e) {
            /*if ($this->get('kernel')->getEnvironment() === 'dev') {
                throw $e;
            }*/
            $event->addMessage(new ResourceMessage(
                'L\'application a rencontré une erreur relative à la base de données. La ressource n\'a pas été sauvegardée.',
                ResourceMessage::TYPE_ERROR
            ));
            return $event;
        }

        return $event->addMessage(new ResourceMessage(
            'La ressource a été sauvegardée avec succès.',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Removes a resource.
     *
     * @param ResourceEvent $event
     *
     * @return ResourceEvent
     */
    private function removeResource(ResourceEvent $event)
    {
        $resource = $event->getResource();
        $this->remove($resource);

        try {
            $this->flush();
        } catch(DBALException $e) {
            /*if ($this->get('kernel')->getEnvironment() === 'dev') {
                throw $e;
            }*/
            if (null !== $previous = $e->getPrevious()) {
                if ($previous instanceof \PDOException && $previous->getCode() == 23000) {
                    return $event->addMessage(new ResourceMessage(
                        'Cette ressource est liée à d\'autres ressources et ne peut pas être supprimée.',
                        ResourceMessage::TYPE_ERROR
                    ));
                }
            }
            return $event->addMessage(new ResourceMessage(
                'L\'application a rencontré une erreur relative à la base de données. La ressource n\'a pas été supprimée.',
                ResourceMessage::TYPE_ERROR
            ));
        }

        return $event->addMessage(new ResourceMessage(
            'La ressource a été supprimée avec succès.',
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
    private function createResourceEvent($resource)
    {
        $event = new ResourceEvent();
        $event->setResource($resource);
        return $event;
    }
}

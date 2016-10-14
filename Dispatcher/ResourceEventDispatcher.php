<?php

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class ResourceEventDispatcher
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEventDispatcher extends EventDispatcher implements ResourceEventDispatcherInterface
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;


    /**
     * Sets the configuration registry.
     *
     * @param ConfigurationRegistry $registry
     */
    public function setConfigurationRegistry($registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function createResourceEvent(ResourceInterface $resource, $throwException = true)
    {
        if ($config = $this->registry->findConfiguration($resource, $throwException)) {
            $class = $config->getEventClass();

            /** @var \Ekyna\Component\Resource\Event\ResourceEventInterface $event */
            $event = new $class;
            $event->setResource($resource);

            return $event;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getResourceEventName(ResourceInterface $resource, $suffix)
    {
        if (null !== $configuration = $this->registry->findConfiguration($resource, false)) {
            return sprintf('%s.%s', $configuration->getResourceId(), $suffix);
        }

        return null;
    }
}

<?php

namespace Ekyna\Component\Resource\Configuration;

use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Class ConfigurationRegistry
 * @package Ekyna\Component\Resource\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationRegistry
{
    /**
     * @var array|ConfigurationInterface[]
     */
    protected $configurations;

    /**
     * @var array
     */
    protected $parentMap;

    /**
     * @var array
     */
    protected $eventPriorityMap;


    /**
     * Constructor.
     *
     * @param array|ConfigurationInterface[]
     */
    public function __construct(array $configurations = [])
    {
        $this->configurations = $configurations;
    }

    /**
     * Adds the resource configuration.
     *
     * @param ConfigurationInterface $configuration
     *
     * @throws \RuntimeException
     */
    public function addConfiguration(ConfigurationInterface $configuration)
    {
        // Clear the parent map.
        $this->parentMap = null;

        $id = $configuration->getResourceId();

        if (array_key_exists($id, $this->configurations)) {
            throw new \RuntimeException(sprintf("Configuration for resource '%s' is already registered.", $id));
        }

        $this->configurations[$id] = $configuration;
    }

    /**
     * Finds a configuration for the given resource (object/class/id)
     *
     * @param mixed   $resource object, class or configuration identifier.
     * @param boolean $throwException
     *
     * @throws \Ekyna\Component\Resource\Exception\NotFoundConfigurationException
     *
     * @return ConfigurationInterface|null
     */
    public function findConfiguration($resource, $throwException = true)
    {
        // By object
        if (is_object($resource)) {
            foreach ($this->configurations as $config) {
                if ($config->isRelevant($resource)) {
                    return $config;
                }
            }
        } elseif (is_string($resource)) {
            // By class
            if (class_exists($resource, false)) {
                foreach ($this->configurations as $config) {
                    if ($resource == $config->getResourceClass()) {
                        return $config;
                    }
                }
            }
            // By Alias
            if ($this->has($resource)) {
                return $this->get($resource);
            }
            // By Id
            foreach ($this->configurations as $config) {
                if ($resource == $config->getResourceId()) {
                    return $config;
                }
            }
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($resource);
        }

        return null;
    }

    /**
     * Finds a configuration for the given translation (object/class)
     *
     * @param mixed   $translation object or class.
     * @param boolean $throwException
     *
     * @throws \Ekyna\Component\Resource\Exception\NotFoundConfigurationException
     *
     * @return ConfigurationInterface|null
     */
    public function findConfigurationByTranslation($translation, $throwException = true)
    {
        if ($translation instanceof TranslationInterface) {
            return $this->findConfiguration($translation->getTranslatable());
        }

        if (is_subclass_of($translation, TranslationInterface::class)) {
            foreach ($this->configurations as $config) {
                if ((null !== $class = $config->getTranslationClass()) && $translation == $class) {
                    return $config;
                }
            }
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($translation);
        }

        return null;
    }

    /**
     * Returns whether a configuration exists or not for the given identifier.
     *
     * @param string $id
     *
     * @return boolean
     */
    public function has($id)
    {
        return array_key_exists($id, $this->configurations);
    }

    /**
     * Returns the configuration for the given identifier.
     *
     * @param string $id
     *
     * @throws \InvalidArgumentException
     *
     * @return ConfigurationInterface
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Configuration "%s" not found.', $id));
        }

        return $this->configurations[$id];
    }

    /**
     * Returns all the ancestors configuration.
     *
     * @param ConfigurationInterface $configuration
     * @param bool                   $included
     *
     * @return ConfigurationInterface[]
     */
    public function getAncestors(ConfigurationInterface $configuration, $included = false)
    {
        $ancestors = [];

        if ($included) {
            $ancestors[$configuration->getResourceName()] = $configuration;
        }

        while (null !== $configuration->getParentId()) {
            $configuration = $this->findConfiguration($configuration->getParentId());
            $ancestors[$configuration->getResourceName()] = $configuration;
        }

        return array_reverse($ancestors);
    }

    /**
     * Returns all the children configuration.
     *
     * @param ConfigurationInterface $configuration
     *
     * @return ConfigurationInterface[]
     */
    public function getChildren(ConfigurationInterface $configuration)
    {
        $children = [];

        foreach ($this->configurations as $child) {
            if ($child->getParentId() === $configuration->getResourceId()) {
                $children[$child->getResourceName()] = $child;
            }
        }

        return $children;
    }

    /**
     * Returns the configurations.
     *
     * @return ConfigurationInterface[]
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Returns the object identity.
     *
     * @param object $object
     *
     * @return \Symfony\Component\Security\Acl\Domain\ObjectIdentity|null
     */
    public function getObjectIdentity($object)
    {
        foreach ($this->configurations as $config) {
            if ($config->isRelevant($object)) {
                return $config->getObjectIdentity();
            }
        }

        return null;
    }

    /**
     * Returns the hierarchy map.
     *
     * @return array
     */
    public function getParentMap()
    {
        if (null !== $this->parentMap) {
            return $this->parentMap;
        }

        return $this->parentMap = $this->buildParentMap();
    }

    /**
     * Returns the event priority map.
     *
     * @return array
     */
    public function getEventPriorityMap()
    {
        if (null !== $this->eventPriorityMap) {
            return $this->eventPriorityMap;
        }

        return $this->eventPriorityMap = $this->buildEventPriorityMap();
    }

    /**
     * Builds the parent map.
     *
     * @return array
     */
    private function buildParentMap()
    {
        $map = [];

        foreach ($this->configurations as $configuration) {
            if (null !== $parentId = $configuration->getParentId()) {
                $map[$configuration->getResourceId()] = $parentId;
            }
        }

        ksort($map);

        return $map;
    }

    /**
     * Builds the event priority map.
     *
     * @return array
     */
    private function buildEventPriorityMap()
    {
        $map = [];

        foreach ($this->configurations as $configuration) {
            if (0 != $priority = $configuration->getEventPriority()) {
                $map[$configuration->getResourceId()] = $priority;
            }
        }

        ksort($map);

        return $map;
    }
}

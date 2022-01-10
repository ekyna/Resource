<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Loader;

use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Behavior\BehaviorBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Config\Registry;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use Ekyna\Component\Resource\Extension\CoreExtension;
use Ekyna\Component\Resource\Extension\ExtensionInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ResourceInterface;

use function array_replace;
use function array_replace_recursive;
use function array_unique;
use function call_user_func;
use function class_exists;
use function is_subclass_of;

/**
 * Class ConfigLoader
 * @package Ekyna\Component\Resource\Config\Loader
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * Stores the non-resolved configurations.
 */
class ConfigLoader
{
    /**
     * The loaded files resources.
     *
     * @var string[]
     */
    private array $files;

    private bool $trackFiles;

    private bool $locked;

    /**
     * @var ExtensionInterface[]
     */
    private array $extensions;

    /**
     * The loaded configurations data.
     *
     * @var array
     */
    private array $data;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->files = [];
        $this->trackFiles = true;
        $this->locked = false;

        $this->extensions = [];
        $this->addExtension(CoreExtension::class);

        $this->data = [
            Registry\PermissionRegistryInterface::NAME => [],
            Registry\ActionRegistryInterface::NAME     => [],
            Registry\BehaviorRegistryInterface::NAME   => [],
            Registry\NamespaceRegistryInterface::NAME  => [],
            Registry\ResourceRegistryInterface::NAME   => [],
        ];
    }

    /**
     * Sets the track resources flag.
     *
     * If you are not using the loaders and therefore don't want
     * to depend on the Config component, set this flag to false.
     *
     * @param bool $track true if you want to track resources, false otherwise
     */
    public function setFileTracking(bool $track): void
    {
        $this->trackFiles = $track;
    }

    /**
     * Checks if resources are tracked.
     *
     * @return bool true if resources are tracked, false otherwise
     */
    public function isTrackingFiles(): bool
    {
        return $this->trackFiles;
    }

    public function setLocked(): void
    {
        $this->locked = true;
    }

    /**
     * Adds the loaded file resource.
     *
     * @param FileResource $resource
     */
    public function addFile(FileResource $resource): void
    {
        if (!$this->trackFiles) {
            return;
        }

        $this->assertNotLocked();

        $this->files[] = $resource;
    }

    /**
     * Returns an array of file resources loaded.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getFiles(): array
    {
        return array_unique($this->files);
    }

    /**
     * Adds the extension class.
     *
     * @param string $class
     */
    public function addExtension(string $class): void
    {
        $this->assertNotLocked();

        if (!is_subclass_of($class, ExtensionInterface::class)) {
            throw new UnexpectedValueException(sprintf(
                'Extension class %s must implements %s.',
                $class,
                ExtensionInterface::class
            ));
        }

        if (isset($this->extensions[$class])) {
            throw new UnexpectedValueException(sprintf('Extension class %s is already registered.', $class));
        }

        $this->extensions[$class] = new $class();
    }

    /**
     * Returns the extension classes.
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Adds the permission configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     */
    public function addPermission(string $name, array $config): array
    {
        $this->assertNotLocked();

        return $this->add(Registry\PermissionRegistryInterface::NAME, $name, $config);
    }

    /**
     * Returns the permission configuration for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getPermission(string $name): array
    {
        return $this->get(Registry\PermissionRegistryInterface::NAME, $name);
    }

    /**
     * Returns all the permissions configurations.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->all(Registry\PermissionRegistryInterface::NAME);
    }

    /**
     * Adds the action configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     * @throws ConfigurationException
     */
    public function addAction(string $name, array $config): array
    {
        $this->assertNotLocked();

        if (class_exists($name)) {
            if (!isset($config['class'])) {
                $config['class'] = $name;
            }
        } elseif (!isset($config['class'])) {
            throw new ConfigurationException("Class is not defined for action '$name'.");
        }

        $class = $config['class'];

        if (is_subclass_of($class, ActionInterface::class)) {
            /** @see ActionInterface::configureAction() */
            $defaults = call_user_func([$class, 'configureAction']);
        } elseif (is_subclass_of($class, ActionBuilderInterface::class)) {
            /** @see ActionBuilderInterface::configureBuilder() */
            $defaults = call_user_func([$class, 'configureBuilder']);
        } else {
            throw new RuntimeException(sprintf(
                'Class %s must implements %s or %s.',
                $class,
                ActionInterface::class,
                ActionBuilderInterface::class
            ));
        }

        $config = array_replace($defaults, $config);

        return $this->add(Registry\ActionRegistryInterface::NAME, $config['name'] ?? $name, $config);
    }

    /**
     * Returns the action configuration for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getAction(string $name): array
    {
        return $this->get(Registry\ActionRegistryInterface::NAME, $name);
    }

    /**
     * Returns all the actions configurations.
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->all(Registry\ActionRegistryInterface::NAME);
    }

    /**
     * Adds the behavior configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     * @throws ConfigurationException
     */
    public function addBehavior(string $name, array $config): array
    {
        $this->assertNotLocked();

        if (class_exists($name)) {
            if (!isset($config['class'])) {
                $config['class'] = $name;
            }
        } elseif (!isset($config['class'])) {
            throw new ConfigurationException("Class is not defined for action '$name'.");
        }

        $class = $config['class'];

        if (is_subclass_of($class, BehaviorInterface::class)) {
            /** @see BehaviorInterface::configureBehavior() */
            $defaults = call_user_func([$class, 'configureBehavior']);
        } elseif (is_subclass_of($class, ActionBuilderInterface::class)) {
            /** @see BehaviorBuilderInterface::configureBuilder() */
            $defaults = call_user_func([$class, 'configureBuilder']);
        } else {
            throw new RuntimeException(sprintf(
                'Class %s must implements %s or %s.',
                $class,
                BehaviorInterface::class,
                BehaviorBuilderInterface::class
            ));
        }

        $config = array_replace($defaults, $config);

        return $this->add(Registry\BehaviorRegistryInterface::NAME, $config['name'] ?? $name, $config);
    }

    /**
     * Returns the behavior configuration for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getBehavior(string $name): array
    {
        return $this->get(Registry\BehaviorRegistryInterface::NAME, $name);
    }

    /**
     * Returns all the behaviors configurations.
     *
     * @return array
     */
    public function getBehaviors(): array
    {
        return $this->all(Registry\BehaviorRegistryInterface::NAME);
    }

    /**
     * Adds the namespace configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     */
    public function addNamespace(string $name, array $config): array
    {
        $this->assertNotLocked();

        return $this->add(Registry\NamespaceRegistryInterface::NAME, $name, $config);
    }

    /**
     * Returns the namespace configuration for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getNamespace(string $name): array
    {
        return $this->get(Registry\NamespaceRegistryInterface::NAME, $name);
    }

    /**
     * Returns all the namespaces configurations.
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->all(Registry\NamespaceRegistryInterface::NAME);
    }

    /**
     * Adds the resource configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     */
    public function addResource(string $name, array $config): array
    {
        $this->assertNotLocked();

        /** @see src/Ekyna/Component/Resource/Config/Builder/ConfigBuilder.php:400 */
        if (!isset($config['interfaces'])) {
            $config['interfaces'] = [];
        }

        // TODO While overriding resource config, 'entity' entry may not be defined
        if (is_array($config['entity']) && isset($config['entity']['interface'])) {
            // TODO Check hierarchy (implementation consistency)
            $config['interfaces'][] = $config['entity']['interface'];
        }

        return $this->add(Registry\ResourceRegistryInterface::NAME, $name, $config);
    }

    /**
     * Returns the resource configuration for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getResource(string $name): array
    {
        return $this->get(Registry\ResourceRegistryInterface::NAME, $name);
    }

    /**
     * Returns all the resources configurations.
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->all(Registry\ResourceRegistryInterface::NAME);
    }

    private function assertNotLocked(): void
    {
        if (!$this->locked) {
            return;
        }

        throw new LogicException('Config loader is locked');
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    private function has(string $type, string $name): bool
    {
        return isset($this->data[$type][$name]);
    }

    /**
     * Adds the configuration for the given type and name.
     *
     * @param string $type
     * @param string $name
     * @param array  $config
     *
     * @return array The resulting config
     */
    private function add(string $type, string $name, array $config): array
    {
        if (isset($this->data[$type][$name])) {
            return $this->data[$type][$name] = array_replace_recursive($this->data[$type][$name], $config);
        }

        return $this->data[$type][$name] = $config;
    }

    /**
     * Returns the configuration for the given type and name.
     *
     * @param string $type
     * @param string $name
     *
     * @return array
     */
    private function get(string $type, string $name): array
    {
        if (!$this->has($type, $name)) {
            throw new NotFoundConfigurationException(
                "There is no configuration registered under the name '$name' for type '$type'."
            );
        }

        return $this->data[$type][$name];
    }

    /**
     * Returns all the configuration for the given type.
     *
     * @param string $type
     *
     * @return array
     */
    private function all(string $type): array
    {
        return $this->data[$type];
    }
}

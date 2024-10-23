<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Builder;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Config\Resolver\ConfigResolver;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\Resolver\OptionsResolver;
use Ekyna\Component\Resource\Exception;
use Ekyna\Component\Resource\Extension\ExtensionInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

use function array_diff;
use function array_keys;
use function array_merge_recursive;
use function array_push;
use function array_replace;
use function array_replace_recursive;
use function call_user_func;
use function class_exists;
use function explode;
use function implode;
use function is_null;
use function is_subclass_of;
use function preg_match;
use function sprintf;
use function str_replace;

/**
 * Class ConfigBuilder
 * @package Ekyna\Component\Resource\Config\Builder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigBuilder
{
    private ConfigLoader   $loader;
    private ConfigResolver $resolver;
    private Inflector      $inflector;

    private bool $built = false;

    private array $permissions;
    private array $actions;
    private array $actionsAliases;
    private array $behaviors;
    private array $behaviorsAliases;
    private array $namespaces;
    private array $resources;
    private array $resourcesAliases;


    /**
     * Constructor.
     *
     * @param ConfigLoader $loader
     */
    public function __construct(ConfigLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Builds the configurations.
     */
    public function build(): void
    {
        $this->resolver = new ConfigResolver($this->loader->getExtensions());
        $this->inflector = InflectorFactory::create()->build();

        $this->buildPermissions();
        $this->buildActions();
        $this->buildBehaviors();
        $this->buildNamespaces();
        $this->buildResources();

        $this->built = true;
    }

    public function finalize(): void
    {
        $this->assertBuilt();

        $this->registerBehaviors();
        $this->registerActions();
        $this->registerPermissions();

        $this->loader->setLocked();
    }

    /**
     * Returns the permissions.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Returns the actions.
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Returns the actions aliases.
     *
     * @return array
     */
    public function getActionsAliases(): array
    {
        return $this->actionsAliases;
    }

    /**
     * Returns the behaviors.
     *
     * @return array
     */
    public function getBehaviors(): array
    {
        return $this->behaviors;
    }

    /**
     * Returns the behaviors aliases.
     *
     * @return array
     */
    public function getBehaviorsAliases(): array
    {
        return $this->behaviorsAliases;
    }

    /**
     * Returns the namespaces.
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Returns the resources.
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Returns the resources aliases.
     *
     * @return array
     */
    public function getResourcesAliases(): array
    {
        return $this->resourcesAliases;
    }

    /**
     * Returns the configurations defaults.
     *
     * @return DefaultsResolver[]
     */
    public function getDefaults(): array
    {
        return $this->resolver->getDefaults();
    }

    /**
     * Returns the extensions.
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->loader->getExtensions();
    }

    /**
     * Returns the files.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->loader->getFiles();
    }

    /**
     * Adds the action class.
     *
     * @param string $class
     *
     * @return array The resolved configuration
     */
    public function addAction(string $class): array
    {
        $this->assertBuilt();

        return $this->registerAction($class);
    }

    /**
     * Adds the behavior class.
     *
     * @param string $class
     *
     * @return array
     */
    public function addBehavior(string $class): array
    {
        $this->assertBuilt();

        return $this->registerBehavior($class);
    }

    /**
     * Registers the action class.
     *
     * @param string $class
     *
     * @return array
     */
    private function registerAction(string $class): array
    {
        $config = $this->loader->addAction($class, []);

        return $this->buildAction($class, $config);
    }

    /**
     * Registers the behavior class.
     *
     * @param string $class
     *
     * @return array
     */
    private function registerBehavior(string $class): array
    {
        $config = $this->loader->addBehavior($class, []);

        return $this->buildBehavior($class, $config);
    }

    /**
     * Asserts the configurations are built.
     */
    private function assertBuilt(): void
    {
        if ($this->built) {
            return;
        }

        throw new Exception\RuntimeException('You must call the \'build\' method first.');
    }

    /**
     * Builds the permissions configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildPermissions(): void
    {
        $data = $this->loader->getPermissions();

        $this->permissions = [];

        foreach ($data as $name => $config) {
            $this->permissions[$name] = $this->resolver->resolvePermissionConfig($config);
        }
    }

    /**
     * Builds the actions configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildActions(): void
    {
        $data = $this->loader->getActions();

        $this->actions = $this->actionsAliases = [];

        foreach ($data as $name => $config) {
            $this->buildAction($name, $config);
        }
    }

    /**
     * Builds the action configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The built configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildAction(string $name, array $config): array
    {
        $resolved = $this->resolver->resolveActionConfig($config, $this->permissions);

        $class = $resolved['class'];
        if (!isset($resolved['name'])) {
            $resolved['name'] = $this->buildName($name);
        }
        $name = $resolved['name'];

        $this->actions[$class] = $resolved;
        $this->actionsAliases[$name] = $class;

        return $resolved;
    }

    /**
     * Builds the behaviors configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildBehaviors(): void
    {
        $data = $this->loader->getBehaviors();

        $this->behaviors = $this->behaviorsAliases = [];

        foreach ($data as $name => $config) {
            $this->buildBehavior($name, $config);
        }
    }

    /**
     * Builds the behavior configuration.
     *
     * @param string $name
     * @param array  $config
     *
     * @return array The built configuration
     *
     * @throws Exception\ConfigurationException
     */
    private function buildBehavior(string $name, array $config): array
    {
        $resolved = $this->resolver->resolveBehaviorConfig($config);

        $class = $resolved['class'];
        if (!isset($resolved['name'])) {
            $resolved['name'] = $this->buildName($name);
        }
        $name = $resolved['name'];

        $this->behaviors[$class] = $resolved;
        $this->behaviorsAliases[$name] = $class;

        return $resolved;
    }

    /**
     * Builds the namespaces configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildNamespaces(): void
    {
        $data = $this->loader->getNamespaces();

        $this->namespaces = [];

        foreach ($data as $name => $config) {
            $this->namespaces[$name] = $this->resolver->resolveNamespaceConfig($config);
        }
    }

    /**
     * Builds the resources configuration.
     *
     * @throws Exception\ConfigurationException
     */
    private function buildResources(): void
    {
        $data = $this->loader->getResources();

        $this->resources = $this->resourcesAliases = [];

        foreach ($data as $name => $config) {
            /** @see src/Ekyna/Component/Resource/Config/Loader/ConfigLoader.php:364 */
            $interfaces = $config['interfaces'];
            unset($config['interfaces']);

            $resolved = $this->resolver->resolveResourceConfig($config, $this->permissions, $this->namespaces);

            $class = $resolved['entity']['class'];

            $this->resources[$class] = $resolved;
            $this->resourcesAliases[$name] = $class;

            foreach ($interfaces as $interface) {
                $this->resourcesAliases[$interface] = $class;
            }
        }
    }

    /**
     * Adds behaviors to the resources configurations based on interfaces.
     *
     * @throws Exception\ConfigurationException
     */
    private function registerBehaviors(): void
    {
        // Normalize configured behaviors
        foreach ($this->resources as $rName => $rConfig) {
            $configured = $rConfig['behaviors'];

            foreach ($configured as $bName => $bConfig) {
                // Use aliases to replace behaviors names with classes
                if (isset($this->behaviorsAliases[$bName])) {
                    unset($configured[$bName]);
                    $configured[$bName = $this->behaviorsAliases[$bName]] = $bConfig;
                }

                // Register unknown behaviors
                if (isset($this->behaviors[$bName])) {
                    continue;
                }

                if (!class_exists($bName)) {
                    throw new Exception\ConfigurationException(
                        "Behavior '$bName' must be register in resources files."
                    );
                }

                $this->registerBehavior($bName);
            }

            $this->resources[$rName]['behaviors'] = $configured;
        }

        $resolver = new OptionsResolver($this->loader->getExtensions(), $this->behaviors);

        // Resolve configured behaviors options
        foreach ($this->resources as $rName => $rConfig) {
            $rBehaviors = $rConfig['behaviors'];

            foreach ($rBehaviors as $bName => $bConfig) {
                try {
                    $rBehaviors[$bName] = $resolver->resolve($bName, (array)$bConfig);
                } catch (ExceptionInterface $exception) {
                    throw Exception\ConfigurationException::create($rName, $bName, $exception);
                }
            }

            $this->resources[$rName]['behaviors'] = $rBehaviors;
        }

        // Add missing behaviors based on interface
        foreach ($this->resources as $rName => $rConfig) {
            $rClass = $rConfig['entity']['class'];
            $rBehaviors = $rConfig['behaviors'];

            foreach ($this->behaviors as $bClass => $bConfig) {
                $interface = $bConfig['interface'];
                if (is_null($interface)) {
                    continue;
                }

                if (isset($rBehaviors[$bClass])) {
                    continue;
                }

                if (is_subclass_of($rClass, $interface)) {
                    try {
                        $rBehaviors[$bClass] = $resolver->resolve($bClass, []);
                    } catch (ExceptionInterface $exception) {
                        throw new Exception\RuntimeException(
                            sprintf(
                                'The class %s implements %s but this behavior can\'t be ' .
                                'configured automatically. Please configure it manually.',
                                $rClass,
                                $interface
                            ), 0, $exception
                        );
                    }
                }
            }

            $this->resources[$rName]['behaviors'] = $rBehaviors;
        }
    }

    /**
     * Adds actions to the resources configurations based on behaviors.
     *
     * @throws Exception\ConfigurationException
     */
    private function registerActions(): void
    {
        // Normalize configured actions
        foreach ($this->resources as $rName => $rConfig) {
            $configured = $rConfig['actions'];
            foreach ($configured as $aName => $aConfig) {
                // Use aliases to replace actions names with classes
                if (isset($this->actionsAliases[$aName])) {
                    unset($configured[$aName]);
                    $configured[$aName = $this->actionsAliases[$aName]] = $aConfig;
                }

                // Register unknown actions
                if (isset($this->actions[$aName])) {
                    continue;
                }

                if (!class_exists($aName)) {
                    throw new Exception\ConfigurationException(
                        "Action '$aName' must be registered in resources files."
                    );
                }

                $this->registerAction($aName);
            }

            $this->resources[$rName]['actions'] = $configured;
        }

        $resolver = new OptionsResolver($this->loader->getExtensions(), $this->actions);

        // Resolve configured actions options
        foreach ($this->resources as $rName => $rConfig) {
            $configured = $rConfig['actions'];
            $resolved = [];

            // First pass: use action builders
            foreach ($configured as $aClass => $aConfig) {
                if (!isset($this->actions[$aClass])) {
                    throw new Exception\RuntimeException("Action '$aClass' does not exist.");
                }

                if (!is_subclass_of($aClass, ActionBuilderInterface::class)) {
                    continue;
                }

                try {
                    $options = $resolver->resolve($aClass, (array)$aConfig);
                } catch (ExceptionInterface $exception) {
                    throw Exception\ConfigurationException::create($rName, $aClass, $exception);
                }

                /** @see ActionBuilderInterface::buildActions() */
                $resolved = array_replace(
                    call_user_func([$aClass, 'buildActions'], $resolver, $rConfig, $options),
                    $resolved
                );

                unset($configured[$aClass]);
            }

            // Second pass: use actions
            foreach ($configured as $aClass => $aConfig) {
                try {
                    $resolved[$aClass] = $resolver->resolve($aClass, (array)$aConfig);
                } catch (ExceptionInterface $exception) {
                    throw Exception\ConfigurationException::create($rName, $aClass, $exception);
                }
            }

            // Third pass: use aliases to replace behaviors names with classes
            $configured = $rConfig['behaviors'];
            foreach ($configured as $bName => $bConfig) {
                if (!isset($this->behaviorsAliases[$bName])) {
                    continue;
                }

                unset($configured[$bName]);
                $configured[$this->behaviorsAliases[$bName]] = $bConfig;
            }

            // Fourth pass: use behaviors
            foreach ($configured as $bClass => $bConfig) {
                if (!isset($this->behaviors[$bClass])) {
                    throw new Exception\RuntimeException("Behavior '$bClass' does not exist.");
                }

                /** @see BehaviorInterface::buildActions() */
                $built = call_user_func([$bClass, 'buildActions'], $resolved, $rConfig, $bConfig);

                if (empty($built)) {
                    continue;
                }

                foreach ($built as $aName => $aConfig) {
                    if (isset($resolved[$aName])) {
                        $aConfig = array_merge_recursive((array)$aConfig, $resolved[$aName]);
                    }

                    try {
                        $resolved[$aName] = $resolver->resolve($aName, (array)$aConfig);
                    } catch (ExceptionInterface $exception) {
                        throw Exception\ConfigurationException::create($rName, $aName, $exception);
                    }
                }
            }

            $this->resources[$rName]['actions'] = $resolved;
        }
    }

    /**
     * Registers the resource's permissions based on actions.
     *
     * @throws Exception\ConfigurationException
     */
    private function registerPermissions(): void
    {
        foreach ($this->resources as $rName => &$rConfig) {
            foreach ($rConfig['actions'] as $aName => $aConfig) {
                if (!isset($this->actions[$aName])) {
                    throw new Exception\ConfigurationException(
                        "Action '$aName' for resource '$rName' is not registered."
                    );
                }

                $aConfig = array_replace_recursive($this->actions[$aName], $aConfig);

                if (empty($permissions = $aConfig['permissions'])) {
                    continue;
                }

                if (!empty($diff = array_diff($permissions, array_keys($this->permissions)))) {
                    throw new Exception\ConfigurationException(
                        sprintf(
                            "Unknown permission '%s'.",
                            implode(', ', $diff)
                        )
                    );
                }

                if (empty($diff = array_diff($permissions, $rConfig['permissions']))) {
                    continue;
                }

                array_push($rConfig['permissions'], ...$diff);
            }
        }
    }

    /**
     * Builds the name.
     *
     * @param string $name
     *
     * @return string
     */
    private function buildName(string $name): string
    {
        if (preg_match(ExtensionInterface::NAME_REGEX, $name)) {
            return $name;
        }

        $name = explode('_', $this->inflector->tableize(str_replace('\\', '', $name)));

        return implode('_', array_diff($name, ['action', 'behavior', 'bundle', 'component']));
    }
}

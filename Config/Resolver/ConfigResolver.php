<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Resolver;

use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Config\Registry;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Extension\ExtensionInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_subclass_of;
use function preg_match;

/**
 * Class ConfigResolver
 * @package Ekyna\Component\Resource\Config\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigResolver
{
    /**
     * @var ExtensionInterface[]
     */
    private array $extensions;

    /**
     * @var DefaultsResolver[]
     */
    private array $defaults;

    private ?OptionsResolver $permissionResolver    = null;
    private ?OptionsResolver $actionResolver        = null;
    private ?OptionsResolver $actionBuilderResolver = null;
    private ?OptionsResolver $behaviorResolver      = null;
    private ?OptionsResolver $namespaceResolver     = null;
    private ?OptionsResolver $resourceResolver      = null;


    /**
     * Constructor.
     *
     * @param ExtensionInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
        $this->defaults = [
            Registry\PermissionRegistryInterface::NAME => null,
            Registry\ActionRegistryInterface::NAME     => null,
            Registry\BehaviorRegistryInterface::NAME   => null,
            Registry\NamespaceRegistryInterface::NAME  => null,
            Registry\ResourceRegistryInterface::NAME   => null,
        ];
    }

    /**
     * Resolves the permission configuration.
     *
     * @param array $options
     *
     * @return array
     * @throws ConfigurationException
     */
    public function resolvePermissionConfig(array $options): array
    {
        try {
            $data = $this->getPermissionResolver()->resolve($options);
        } catch (ExceptionInterface $exception) {
            throw new ConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        // TODO Remove defaults

        return $data;
    }

    /**
     * Resolves the action configuration.
     *
     * @param array $options
     * @param array $permissions
     *
     * @return array
     * @throws ConfigurationException
     */
    public function resolveActionConfig(array $options, array $permissions): array
    {
        if (!isset($options['class'])) {
            throw new ConfigurationException('Action class is not defined.');
        }

        if (is_subclass_of($options['class'], ActionInterface::class)) {
            try {
                $data = $this->getActionResolver()->resolve($options);
            } catch (ExceptionInterface $exception) {
                throw new ConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
            }

            if (isset($data['permission']) && !isset($permissions[$data['permission']])) {
                throw new ConfigurationException(sprintf("Unknown permission '%s'.", $data['permission']));
            }

            // TODO Remove defaults

            return $data;
        }

        if (is_subclass_of($options['class'], ActionBuilderInterface::class)) {
            return $this->getActionBuilderResolver()->resolve($options);
        }

        throw new ConfigurationException(sprintf(
            'Action class %s must implements %s or %s',
            $options['class'],
            ActionInterface::class,
            ActionBuilderInterface::class
        ));
    }

    /**
     * Resolves the behavior configuration.
     *
     * @param array $options
     *
     * @return array
     * @throws ConfigurationException
     */
    public function resolveBehaviorConfig(array $options): array
    {
        try {
            $data = $this->getBehaviorResolver()->resolve($options);
        } catch (ExceptionInterface $exception) {
            throw new ConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        // TODO Remove defaults

        return $data;
    }

    /**
     * Resolves the namespace configuration.
     *
     * @param array $options
     *
     * @return array
     * @throws ConfigurationException
     */
    public function resolveNamespaceConfig(array $options): array
    {
        try {
            $data = $this->getNamespaceResolver()->resolve($options);
        } catch (ExceptionInterface $exception) {
            throw new ConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        // TODO Remove defaults

        return $data;
    }

    /**
     * Resolves the resource configuration.
     *
     * @param array $options
     * @param array $permissions
     * @param array $namespaces
     *
     * @return array
     * @throws ConfigurationException
     */
    public function resolveResourceConfig(array $options, array $permissions, array $namespaces): array
    {
        try {
            $data = $this->getResourceResolver()->resolve($options);
        } catch (ExceptionInterface $exception) {
            throw new ConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!isset($namespaces[$name = $data['namespace']])) {
            throw new ConfigurationException(sprintf("Unknown namespace '%s'.", $name));
        }

        if (isset($data['permissions'])) {
            foreach ($data['permissions'] as $name) {
                if (!isset($permissions[$name])) {
                    throw new ConfigurationException(sprintf("Unknown permission '%s'.", $name));
                }
            }
        }

        // TODO Remove defaults

        return $data;
    }

    /**
     * Returns the configurations defaults.
     *
     * @return DefaultsResolver[]
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Returns the permission options resolver.
     *
     * @return OptionsResolver
     */
    private function getPermissionResolver(): OptionsResolver
    {
        if (null !== $this->permissionResolver) {
            return $this->permissionResolver;
        }

        $resolver = new OptionsResolver();
        $defaults = new DefaultsResolver();

        foreach ($this->extensions as $extension) {
            $extension->extendPermissionConfig($resolver, $defaults);
        }

        $this->defaults[Registry\PermissionRegistryInterface::NAME] = $defaults;

        return $this->permissionResolver = $resolver;
    }

    /**
     * Returns the action options resolver.
     *
     * @return OptionsResolver
     */
    private function getActionResolver(): OptionsResolver
    {
        if (null !== $this->actionResolver) {
            return $this->actionResolver;
        }

        $resolver = new OptionsResolver();
        $defaults = new DefaultsResolver();

        foreach ($this->extensions as $extension) {
            $extension->extendActionConfig($resolver, $defaults);
        }

        $this->defaults[Registry\ActionRegistryInterface::NAME] = $defaults;

        return $this->actionResolver = $resolver;
    }

    /**
     * Returns the action builder options resolver.
     *
     * @return OptionsResolver
     */
    private function getActionBuilderResolver(): OptionsResolver
    {
        if (null !== $this->actionBuilderResolver) {
            return $this->actionBuilderResolver;
        }

        // TODO Make configurable / move to CoreExtension ?
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['name', 'class'])
            ->setAllowedTypes('name', 'string')
            ->setAllowedValues('name', function ($value) {
                if (!preg_match('~^[a-z][a-z0-9]*(_[a-z0-9]+)*[a-z0-9]*$~', $value)) {
                    throw new InvalidOptionsException("Invalid action name '$value'.");
                }

                return true;
            });

        return $this->actionBuilderResolver = $resolver;
    }

    /**
     * Returns the behavior options resolver.
     *
     * @return OptionsResolver
     */
    private function getBehaviorResolver(): OptionsResolver
    {
        if (null !== $this->behaviorResolver) {
            return $this->behaviorResolver;
        }

        $resolver = new OptionsResolver();
        $defaults = new DefaultsResolver();

        foreach ($this->extensions as $extension) {
            $extension->extendBehaviorConfig($resolver, $defaults);
        }

        $this->defaults[Registry\BehaviorRegistryInterface::NAME] = $defaults;

        return $this->behaviorResolver = $resolver;
    }

    /**
     * Returns the namespace options resolver.
     *
     * @return OptionsResolver
     */
    private function getNamespaceResolver(): OptionsResolver
    {
        if (null !== $this->namespaceResolver) {
            return $this->namespaceResolver;
        }

        $resolver = new OptionsResolver();
        $defaults = new DefaultsResolver();

        foreach ($this->extensions as $extension) {
            $extension->extendNamespaceConfig($resolver, $defaults);
        }

        $this->defaults[Registry\NamespaceRegistryInterface::NAME] = $defaults;

        return $this->namespaceResolver = $resolver;
    }

    /**
     * Returns the resource options resolver.
     *
     * @return OptionsResolver
     */
    private function getResourceResolver(): OptionsResolver
    {
        if (null !== $this->resourceResolver) {
            return $this->resourceResolver;
        }

        $resolver = new OptionsResolver();
        $defaults = new DefaultsResolver();

        foreach ($this->extensions as $extension) {
            $extension->extendResourceConfig($resolver, $defaults);
        }

        $this->defaults[Registry\ResourceRegistryInterface::NAME] = $defaults;

        return $this->resourceResolver = $resolver;
    }
}

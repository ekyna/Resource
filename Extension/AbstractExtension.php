<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Extension;

use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Symfony\Component\DependencyInjection as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function class_exists;
use function interface_exists;
use function is_null;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Class AbstractExtension
 * @package Ekyna\Component\Resource\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExtension implements ExtensionInterface
{
    public function extendPermissionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
    }

    public function extendNamespaceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
    }

    public function extendActionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
    }

    public function extendActionOptions(OptionsResolver $resolver): void
    {
    }

    public function extendBehaviorConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
    }

    public function extendBehaviorOptions(OptionsResolver $resolver): void
    {
    }

    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
    }

    public function configureParameters(ContainerBuilder $container): void
    {
    }

    public function configurePasses(ContainerBuilder $container, RegistryFactoryInterface $factory): void
    {
    }

    public function configureContainer(DI\ContainerBuilder $container, RegistryFactoryInterface $factory): void
    {
    }

    /**
     * Validates the given class.
     *
     * @param string|null $value     The configured class
     * @param string|null $interface The interface the configured class must implement
     * @param bool        $required  Whether a class is required
     */
    protected function validateClass(?string $value, string $interface = null, bool $required = true): bool
    {
        // Requirement
        if (empty($value)) {
            if (!$required) {
                return true;
            }

            throw new InvalidOptionsException('Class must be defined');
        }

        // Class must exist
        if (!class_exists($value)) {
            throw new InvalidOptionsException(sprintf('Class %s does not exist.', $value));
        }

        // Class must implements interface
        if (!empty($interface) && !is_subclass_of($value, $interface)) {
            throw new InvalidOptionsException(sprintf(
                'Class %s must implements %s.',
                $value,
                $interface
            ));
        }

        return true;
    }

    /**
     * Validates the given interface.
     *
     * @param string|null $value    The configured interface
     * @param string|null $default  The default interface
     * @param bool        $required Whether an interface is required
     */
    protected function validateInterface(?string $value, string $default = null, bool $required = true): bool
    {
        // Requirement
        if (empty($value)) {
            if (!$required) {
                return true;
            }

            throw new InvalidOptionsException('Class must be defined');
        }

        // Interface must exist
        if (!interface_exists($value)) {
            throw new InvalidOptionsException(sprintf('Interface %s does not exist.', $value));
        }

        // Configured interface must extend the default interface
        if (!empty($default) && !is_subclass_of($value, $default)) {
            throw new InvalidOptionsException(sprintf('Interface %s must extends %s.', $value, $default));
        }

        return true;
    }

    /**
     * Validates configured class and interface.
     *
     * @param array|string|null $value     The configured class and interface.
     * @param string            $interface The default interface the configured class must implement
     *                                     and the configured interface must extend.
     * @param bool              $required  Whether at least a class is required.
     */
    protected function validateClassInterface($value, string $interface, bool $required = true): bool
    {
        if (empty($value)) {
            if ($required) {
                return false;
            }

            return true;
        }

        // Class only
        if (is_string($value)) {
            return $this->validateClass($value, $interface);
        }

        if (!isset($value['class'])) {
            throw new InvalidOptionsException('Class must be defined');
        }

        // If interface is not configured
        if (!isset($value['interface'])) {
            return $this->validateClass($value['class'], $interface);
        }

        $this->validateInterface($value['interface'], $interface);

        return $this->validateClass($value['class'], $value['interface']);
    }

    /**
     * Normalizes the configured class.
     *
     * @param string|null $value     The configured class and interface.
     * @param string      $interface The default interface the configured class must implement.
     * @param string|null $class     The default class.
     */
    protected function normalizeClass(?string $value, string $interface, string $class = null): ?string
    {
        if (empty($value)) {
            if (empty($class)) {
                return null;
            }

            $value = $class;
        }

        // CLass must implement the default interface
        if (!is_subclass_of($value, $interface)) {
            throw new InvalidOptionsException(sprintf(
                'Class %s must implements %s.',
                $value, $interface
            ));
        }

        return $value;
    }

    /**
     * Normalizes the configured class and interface.
     *
     * @param array|string|null $value     The configured class and interface
     * @param string            $interface The default interface the configured class must implement.
     */
    protected function normalizeInterface($value, string $interface): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = ['class' => $value];
        }

        $value = array_replace([
            'interface' => null,
        ], $value);

        if (isset($value['interface'])) {
            // Interface must extend the default interface
            if (!is_subclass_of($value['interface'], $interface)) {
                throw new InvalidOptionsException(sprintf(
                    'Interface %s must extends %s.',
                    $value['interface'], $interface
                ));
            }
        }

        if (isset($value['class'])) {
            // Class must extend the interface
            $interface = $value['interface'] ?: $interface;
            if (!is_subclass_of($value['class'], $interface)) {
                throw new InvalidOptionsException(sprintf(
                    'Class %s must implements %s.',
                    $value['class'], $interface
                ));
            }
        }

        return $value;
    }

    /**
     * Normalizes the configured class and interface.
     *
     * @param array|string|null $value     The configured class and interface
     * @param string            $interface The interface the configured class must implement
     *                                     and the configured interface must extend.
     * @param string|null       $default   The default class
     */
    protected function normalizeClassInterface($value, string $interface, string $default = null): ?array
    {
        if (empty($value)) {
            if (empty($default)) {
                return null;
            }

            $value = $default;
        }

        if (is_string($value)) {
            $value = ['class' => $value];
        }

        $value = array_replace([
            'interface' => null,
            'class'     => $default,
        ], $value);

        if (isset($value['interface'])) {
            // Interface must extend the default interface
            if (!is_subclass_of($value['interface'], $interface)) {
                throw new InvalidOptionsException(sprintf(
                    'Interface %s must extends %s.',
                    $value['interface'], $interface
                ));
            }
        }

        if (isset($value['class'])) {
            // Class must extend the interface
            $interface = $value['interface'] ?: $interface;
            if (!is_subclass_of($value['class'], $interface)) {
                throw new InvalidOptionsException(sprintf(
                    'Class %s must implements %s.',
                    $value['class'], $interface
                ));
            }
        }

        return $value;
    }
}

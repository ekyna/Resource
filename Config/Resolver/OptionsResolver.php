<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Resolver;

use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Extension\ExtensionInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyResolver;

use function call_user_func;
use function is_subclass_of;

/**
 * Class OptionsResolver
 * @package Ekyna\Component\Resource\Config\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionsResolver
{
    /** @var array<ExtensionInterface> */
    private array $extensions;
    private array $configs;
    /** @var array<SymfonyResolver> */
    private array $resolvers;

    public function __construct(array $extensions, array $configs)
    {
        $this->extensions = $extensions;
        $this->configs = $configs;
        $this->resolvers = [];
    }

    /**
     * Resolves the options for the given configuration name.
     *
     * @throws ConfigurationException
     * @throws ExceptionInterface
     */
    public function resolve(string $name, array $options): array
    {
        if (!isset($this->resolvers[$name])) {
            if (!isset($this->configs[$name])) {
                throw new ConfigurationException("Failed to find configuration for '$name'.'");
            }

            $resolver = new SymfonyResolver();

            $class = $this->configs[$name]['class'];
            if (is_subclass_of($class, ActionInterface::class)) {
                foreach ($this->extensions as $extension) {
                    $extension->extendActionOptions($resolver);
                }
            } elseif (is_subclass_of($class, BehaviorInterface::class)) {
                foreach ($this->extensions as $extension) {
                    $extension->extendBehaviorOptions($resolver);
                }
            }

            /** @see ActionInterface::configureOptions() */
            /** @see BehaviorInterface::configureOptions() */
            /** @see ActionBuilderInterface::configureOptions() */
            call_user_func([$class, 'configureOptions'], $resolver);

            $this->resolvers[$name] = $resolver;
        }

        return $this->resolvers[$name]->resolve($options);
    }
}

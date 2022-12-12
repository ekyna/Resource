<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Action\ActionInterface;
use Ekyna\Component\Resource\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function array_map;
use function array_merge;
use function array_unique;
use function class_uses;
use function get_parent_class;
use function in_array;
use function is_array;

/**
 * Class ActionAutoConfigurePass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class ActionAutoConfigurePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (empty($configureMap = $this->getAutoconfigureMap())) {
            throw new LogicException(sprintf(
                'Method %s::getMap should return the action au configuration.',
                static::class
            ));
        }

        foreach ($container->findTaggedServiceIds(ActionInterface::DI_TAG, true) as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            if (!$definition->isAutoconfigured()) {
                continue;
            }

            $traits = $this->getTraits($definition->getClass());

            foreach ($configureMap as $trait => $calls) {
                if (!in_array($trait, $traits, true)) {
                    continue;
                }

                foreach ($calls as $setter => $arguments) {
                    if (!is_array($arguments)) {
                        $arguments = [$arguments];
                    }

                    $arguments = array_map(function (Reference|string $arg) {
                        if ($arg instanceof Reference) {
                            return $arg;
                        }

                        return new Reference($arg);
                    }, $arguments);

                    $definition->addMethodCall($setter, $arguments);
                }
            }
        }
    }

    /**
     * Returns the traits used by the given class.
     *
     * @param string $class
     * @param bool   $autoload
     *
     * @return array
     */
    private function getTraits(string $class, bool $autoload = true): array
    {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    /**
     * Returns the actions autoconfiguration to apply.
     *
     * @return array<string, array<string, Reference|string|array<int, Reference|string>>>
     */
    abstract protected function getAutoconfigureMap(): array;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function sprintf;

/**
 * Class SearchPass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SearchPass implements CompilerPassInterface
{
    public function __construct(
        private readonly RegistryFactoryInterface $factory
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $indexes = $container->getDefinition('fos_elastica.config_source.container')->getArgument(0);

        foreach ($this->factory->getResourceRegistry()->all() as $resource) {
            $id = $resource->getId();

            $listenerId = sprintf('fos_elastica.listener.%s', $id);

            if (!$container->hasDefinition($listenerId)) {
                continue;
            }

            $definition = $container->getDefinition($listenerId);

            $stop = true;
        }
    }
}

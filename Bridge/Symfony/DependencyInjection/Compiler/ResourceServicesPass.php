<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function sprintf;

/**
 * Class ResourceServicesPass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceServicesPass implements CompilerPassInterface
{
    private string $tag;
    private string $name;

    public function __construct(string $tag, string $name)
    {
        $this->tag = $tag;
        $this->name = $name;
    }

    public function process(ContainerBuilder $container): void
    {
        $services = [];
        foreach ($container->findTaggedServiceIds($this->tag, true) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['resource'])) {
                    continue;
                }

                $services[$tag['resource']] = new Reference($serviceId);

                continue 2;
            }

            throw new InvalidArgumentException(sprintf(
                'Service %s with tag \'%s\' does not have \'resource\' attribute.',
                $serviceId, $this->tag
            ));
        }

        $container
            ->getDefinition($this->name . '.factory')
            ->replaceArgument(1, ServiceLocatorTagPass::register($container, $services, $this->name));
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function realpath;

/**
 * Class ConfigureValidatorPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigureValidatorPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('validator.builder');

        $definition->addMethodCall('addXmlMappings', [
            [realpath(__DIR__ . '/../../Resources/config/validation.xml')]
        ]);
    }
}

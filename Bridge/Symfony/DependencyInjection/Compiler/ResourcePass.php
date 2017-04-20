<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection as DI;

/**
 * Class ResourcePass
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourcePass implements DI\Compiler\CompilerPassInterface
{
    private ContainerBuilder $builder;

    public function __construct(ContainerBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function process(DI\ContainerBuilder $container): void
    {
        $this->builder->configureServices($container);
    }
}

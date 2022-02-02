<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Resource\Exception\LogicException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_fill_keys;
use function array_keys;
use function array_replace;
use function is_callable;

/**
 * Class ContainerHelper
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ContainerHelper
{
    private const SERVICE_CONFIG_SHAPE = [
        'id'                => 'string',
        'class'             => 'string',
        'interface'         => 'string|null',
        'default_class'     => 'string|null',
        'default_interface' => 'string|null',
        'configure_found'   => 'callable|null',
        'configure_created' => 'callable|null',
        'create'            => 'callable|null',
        'configure'         => 'callable|null',
    ];

    public static function configureService(ContainerBuilder $container, array $config): Definition
    {
        if (!isset($config['id'])) {
            throw new LogicException('Service id must be configured.');
        }
        if (!isset($config['class'])) {
            throw new LogicException('Service class must be configured.');
        }

        $config = array_replace(array_fill_keys(array_keys(self::SERVICE_CONFIG_SHAPE), null), $config);

        $customClass = $config['default_class']
            && ($config['class'] !== $config['default_class']);

        $customInterface = $config['interface']
            && $config['default_interface']
            && ($config['interface'] !== $config['default_interface']);

        // Definition
        $name = $config['id'];
        $definition = null;
        if ($container->hasDefinition($config['id'])) {
            $definition = $container->getDefinition($config['id']);
        } elseif ($customClass && $container->hasDefinition($config['class'])) {
            $definition = $container->getDefinition($config['class']);
            $name = $config['class'];
        } elseif ($customInterface && $container->hasDefinition($config['interface'])) {
            $definition = $container->getDefinition($config['interface']);
            $name = $config['interface'];
        }

        if ($definition) {
            // Change class if redefined
            if ($definition->getClass() !== $config['class']) {
                $definition->setClass($config['class']);
            }

            // Configure found definition with callable
            is_callable($config['configure_found']) && $config['configure_found']($definition);
        } else {
            if (is_callable($config['create'])) {
                // Create definition with callable
                $definition = $config['create']();
            } else {
                // Create definition
                $definition = new Definition($config['class']);
            }

            $container->setDefinition($config['id'], $definition);

            // Configure created definition with callable
            is_callable($config['configure_created']) && $config['configure_created']($definition);
        }

        // Configure definition with callable
        is_callable($config['configure']) && $config['configure']($definition);

        /**
         * TODO If a service has been auto-registered with App\Repository\PostRepository ID,
         *  we need to replace its ID with 'ekyna_blog.repository.post' (instead of creating wrong alias).
         */

        // Id alias
        if ($name !== $config['id'] && !$container->hasAlias($config['id'])) {
            $container->setAlias($config['id'], $name);
        }

        // Class alias
        if ($customClass && $name !== $config['class'] && !$container->hasAlias($config['class'])) {
            $container->setAlias($config['class'], $name);
        }

        // Interface alias
        if ($customInterface && $name !== $config['interface'] && !$container->hasAlias($config['interface'])) {
            $container->setAlias($config['interface'], $name);
        }

        return $definition;
    }
}

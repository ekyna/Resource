<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

use Ekyna\Component\Resource\Config\Resolver\OptionsResolver;
use Ekyna\Component\Resource\Exception\LogicException;

use function sprintf;

/**
 * Class AbstractActionBuilder
 * @package Ekyna\Component\Resource\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractActionBuilder implements ActionBuilderInterface
{
    protected const NAME = null;

    public static function buildActions(OptionsResolver $resolver, array $config, array $options): array
    {
        $actions = [];

        foreach (static::getMap($config) as $name => $class) {
            if (isset($options[$name]) && false === $options[$name]) {
                continue;
            }

            $actionOptions = static::buildActionOptions($options, $name);

            $actions[$class] = $resolver->resolve($class, $actionOptions);
        }

        return $actions;
    }

    public static function configureBuilder(): array
    {
        if (empty(static::NAME)) {
            throw new LogicException(sprintf('You must define the %s::NAME constant.', static::class));
        }

        return [
            'name' => static::NAME,
        ];
    }

    /**
     * Returns the actions map : [name => class]
     */
    abstract protected static function getMap(array $config): array;

    /**
     * Build action options for the given name.
     */
    protected static function buildActionOptions(array $all, string $name): array
    {
        $options = $all[$name] ?? [];

        if (true === $options) {
            $options = [];
        }

        return $options;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

use Ekyna\Component\Resource\Config\Resolver\OptionsResolver as ResourceOptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface ActionBuilderInterface
 * @package Ekyna\Component\Resource\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ActionBuilderInterface
{
    public const DI_TAG = 'ekyna_resource.action_builder';

    /**
     * Builds the actions.
     *
     * @param ResourceOptionsResolver $resolver The resource options resolver
     * @param array                   $config   The resource raw configuration
     * @param array                   $options  The action options
     *
     * @return array The built actions.
     */
    public static function buildActions(ResourceOptionsResolver $resolver, array $config, array $options): array;

    /**
     * Returns the action builder configuration.
     * Keys 'name' and 'label' must be defined and non-empty.
     *
     * @return array
     */
    public static function configureBuilder(): array;

    /**
     * Configures the action options.
     *
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver): void;
}

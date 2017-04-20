<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

use Ekyna\Component\Resource\Config\ActionConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface ActionInterface
 * @package Ekyna\Component\Resource\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ActionInterface
{
    public const DI_TAG = 'ekyna_resource.action';

    /**
     * Sets the config.
     *
     * @param ActionConfig $config
     *
     * @return ActionInterface
     */
    public function setConfig(ActionConfig $config): ActionInterface;

    /**
     * Sets the request.
     *
     * @param Request $request
     *
     * @return ActionInterface
     */
    public function setRequest(Request $request): ActionInterface;

    /**
     * Sets the context.
     *
     * @param Context $context
     *
     * @return ActionInterface
     */
    public function setContext(Context $context): ActionInterface;

    /**
     * Sets the options.
     *
     * @param array $options
     *
     * @return ActionInterface
     */
    public function setOptions(array $options): ActionInterface;

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function configureAction(): array;

    /**
     * Configures options that must be defined on resources.
     *
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver): void;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ResourceHelperInterface
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceHelperInterface
{
    /**
     * Returns the action config for the given name.
     */
    public function getActionConfig(string $action): ActionConfig;

    /**
     * Returns the configuration for the resource.
     */
    public function getResourceConfig(ResourceInterface|string $resource): ResourceConfig;

    /**
     * Returns the resource has the given action.
     */
    public function hasAction(ResourceInterface|string $resource, string $action): bool;

    public function getManager(string $class): ResourceManagerInterface;

    /**
     * Generates path or URL for the given resource and action.
     */
    public function generateResourcePath(
        string|ResourceInterface $resource,
        string $action,
        array $parameters = [],
        bool $absolute = false
    ): string;
}

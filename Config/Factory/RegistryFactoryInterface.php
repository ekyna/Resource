<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Factory;

use Ekyna\Component\Resource\Config\Registry;

/**
 * Class RegistryFactoryInterface
 * @package Ekyna\Component\Resource\Config\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RegistryFactoryInterface
{
    /**
     * Returns the permission registry.
     */
    public function getPermissionRegistry(): Registry\PermissionRegistryInterface;

    /**
     * Returns the action registry.
     */
    public function getActionRegistry(): Registry\ActionRegistryInterface;

    /**
     * Returns the behavior registry.
     */
    public function getBehaviorRegistry(): Registry\BehaviorRegistryInterface;

    /**
     * Returns the namespace registry.
     */
    public function getNamespaceRegistry(): Registry\NamespaceRegistryInterface;

    /**
     * Returns the resource registry.
     */
    public function getResourceRegistry(): Registry\ResourceRegistryInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Manager;

/**
 * Interface ManagerFactoryInterface
 * @package  Ekyna\Component\Resource\Manager
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface ManagerFactoryInterface
{
    /**
     * Registers the adapter.
     */
    public function registerAdapter(AdapterInterface $adapter): void;

    /**
     * Registers the resource manager for the given resource class.
     *
     * @param string                   $resource The resource class.
     * @param ResourceManagerInterface $manager  The resource manager.
     */
    public function registerManager(string $resource, ResourceManagerInterface $manager): void;

    /**
     * Returns the resource manager for the given resource class.
     *
     * @param class-string<T> $resource The resource class.
     *
     * @return ResourceManagerInterface<T>
     */
    public function getManager(string $resource): ResourceManagerInterface;
}

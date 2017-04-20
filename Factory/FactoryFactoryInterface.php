<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Factory;

/**
 * Interface FactoryFactoryInterface
 * @package  Ekyna\Component\Resource\Factory
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface FactoryFactoryInterface
{
    /**
     * Registers the adapter.
     */
    public function registerAdapter(AdapterInterface $adapter): void;

    /**
     * Returns the resource factory for the given resource class.
     *
     * @param string                $resource The resource class.
     *
     * @return ResourceFactoryInterface
     *
     * @psalm-param class-string<T> $resource
     * @psalm-return ResourceFactoryInterface<T>
     */
    public function getFactory(string $resource): ResourceFactoryInterface;
}

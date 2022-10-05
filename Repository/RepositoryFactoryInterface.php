<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

/**
 * Interface RepositoryFactoryInterface
 * @package  Ekyna\Component\Resource\Repository
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface RepositoryFactoryInterface
{
    /**
     * Registers the adapter.
     */
    public function registerAdapter(AdapterInterface $adapter): void;

    /**
     * Returns the repository for the given resource class.
     *
     * @param class-string<T> $resource The resource class.
     *
     * @return ResourceRepositoryInterface<T>
     */
    public function getRepository(string $resource): ResourceRepositoryInterface;
}

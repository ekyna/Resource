<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Pagerfanta\Pagerfanta;

/**
 * Interface ResourceRepositoryInterface
 * @package  Ekyna\Component\Resource\Repository
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template R
 */
interface ResourceRepositoryInterface
{
    public const DI_TAG = 'ekyna_resource.repository';

    /**
     * Finds the resource by its ID.
     *
     * @return R|null
     */
    public function find(int $id): ?ResourceInterface;

    /**
     * Finds all resources.
     *
     * @return array<R>|Paginator<R>
     */
    public function findAll(): array|Paginator;

    /**
     * Finds one resource by criteria and sorting.
     *
     * @return R|null
     */
    public function findOneBy(array $criteria, array $sorting = []): ?ResourceInterface;

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @return array<R>|Paginator<R>
     */
    public function findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null): array|Paginator;

    /**
     * Finds a random resource by criteria.
     *
     * @param array $criteria
     *
     * @return ResourceInterface|null
     * @TODO Remove
     * @deprecated
     */
    public function findRandomOneBy(array $criteria);

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return ResourceInterface[]
     * @TODO Remove
     * @deprecated
     */
    public function findRandomBy(array $criteria, int $limit);

    /**
     * Creates a pager.
     *
     * @return Pagerfanta<R>
     */
    public function createPager(array $criteria = [], array $sorting = []): Paginator;

    /**
     * Returns the (doctrine) pager.
     *
     * @return Pagerfanta<R>
     */
    public function getPager(Query|QueryBuilder $query): Paginator;

    /**
     * Returns the resource class name.
     */
    public function getClassName(): string;
}

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
 * @TODO     PHP8 Type hinting
 *
 * @template T
 */
interface ResourceRepositoryInterface
{
    public const DI_TAG = 'ekyna_resource.repository';

    /**
     * Finds the resource by its ID.
     *
     * @param int $id
     *
     * @return T|ResourceInterface|null
     */
    public function find(int $id): ?ResourceInterface;

    /**
     * Finds all resources.
     *
     * @return array<T|ResourceInterface>|Paginator
     */
    public function findAll();

    /**
     * Finds one resource by criteria and sorting.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return T|ResourceInterface|null
     */
    public function findOneBy(array $criteria, array $sorting = []): ?ResourceInterface;

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @param array    $criteria
     * @param array    $sorting
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<T|ResourceInterface>|Paginator
     */
    public function findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null);

    /**
     * Finds a random resource by criteria.
     *
     * @param array $criteria
     *
     * @return ResourceInterface|null
     */
    public function findRandomOneBy(array $criteria);

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return ResourceInterface[]
     */
    public function findRandomBy(array $criteria, int $limit);

    /**
     * Creates a pager.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return Pagerfanta<T|ResourceInterface>
     */
    public function createPager(array $criteria = [], array $sorting = []): Pagerfanta;

    /**
     * Returns the (doctrine) pager.
     *
     * @param Query|QueryBuilder $query
     *
     * @return Pagerfanta<T|ResourceInterface>
     */
    public function getPager($query): Pagerfanta;

    /**
     * Returns the resource class name.
     *
     * @return string
     */
    public function getClassName(): string;
}

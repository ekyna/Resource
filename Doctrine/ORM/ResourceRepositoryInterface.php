<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

/**
 * Interface ResourceRepositoryInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns a new resource instance.
     *
     * @return object
     */
    public function createNew();

    /**
     * Finds the resource by his ID.
     *
     * @param int $id
     *
     * @return null|object
     */
    public function find($id);

    /**
     * Finds all resources.
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findAll();

    /**
     * Finds one resource by criteria and sorting.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return null|object
     */
    public function findOneBy(array $criteria, array $sorting = null);

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @param array $criteria
     * @param array $sorting
     * @param int   $limit
     * @param int   $offset
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findBy(array $criteria, array $sorting = null, $limit = null, $offset = null);

    /**
     * Finds a random resource by criteria.
     *
     * @param array $criteria
     *
     * @return null|object
     */
    public function findRandomOneBy(array $criteria);

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findRandomBy(array $criteria, $limit);

    /**
     * Creates a pager.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function createPager(array $criteria = [], array $sorting = []);

    /**
     * Returns the (doctrine) pager.
     *
     * @param \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $query
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getPager($query);
}

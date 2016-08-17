<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Trait ResourceRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 *
 * @method string getClassName()
 * @method QueryBuilder createQueryBuilder($alias)
 */
trait ResourceRepositoryTrait
{
    /**
     * Creates a new resource.
     *
     * @return object
     */
    public function createNew()
    {
        $class = $this->getClassName();
        return new $class;
    }

    /**
     * Finds the resource by his ID.
     *
     * @param int $id
     *
     * @return null|object
     */
    public function find($id)
    {
        return $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias().'.id = '.intval($id))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds all resources.
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findAll()
    {
        return $this
            ->getCollectionQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Finds one resource by criteria and sorting.
     *
     * @param array $criteria
     *
     * @return null|object
     */
    public function findOneBy(array $criteria = [])
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

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
    public function findBy(array $criteria = [], array $sorting = [], $limit = null, $offset = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        $query = $queryBuilder->getQuery();

        if (null !== $limit) {
            return $this->collectionResult($query);
        }

        return $query->getResult();
    }

    /**
     * Finds a random resource by criteria.
     *
     * @param array $criteria
     *
     * @return null|object
     */
    public function findRandomOneBy(array $criteria)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findRandomBy(array $criteria, $limit)
    {
        $limit = intval($limit);
        if ($limit <= 1) {
            throw new \InvalidArgumentException('Please use `findRandomOneBy()` for single result.');
        }

        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        $query = $queryBuilder
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $this->collectionResult($query);
    }

    /**
     * Creates a pager.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return Pagerfanta
     */
    public function createPager(array $criteria = [], array $sorting = [])
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPager($queryBuilder);
    }

    /**
     * Returns the (doctrine) pager.
     *
     * @param \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $query
     *
     * @return Pagerfanta
     */
    public function getPager($query)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query, true, false));
        return $pager->setNormalizeOutOfRangePages(true);
    }

    /**
     * Returns th (array) pager.
     *
     * @param array $objects
     *
     * @return Pagerfanta
     */
    public function getArrayPager(array $objects)
    {
        $pager = new Pagerfanta(new ArrayAdapter($objects));
        return $pager->setNormalizeOutOfRangePages(true);
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * Returns the collection query builder.
     *
     * @return QueryBuilder
     */
    protected function getCollectionQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * Applies the criteria to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = [])
    {
        foreach ($criteria as $property => $value) {
            $name = $this->getPropertyName($property);
            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':'.$parameter))
                    ->setParameter($parameter, $value)
                ;
            }
        }
    }

    /**
     * Applies the sorting to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = [])
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    /**
     * Returns the property name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName($name)
    {
        if (false === strpos($name, '.')) {
            return $this->getAlias().'.'.$name;
        }
        return $name;
    }

    /**
     * Returns the collection results.
     *
     * @param Query $query
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function collectionResult(Query $query)
    {
        return $query->getResult();
    }

    /**
     * Returns the alias.
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'o';
    }
}

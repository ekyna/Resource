<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Trait ResourceRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Util
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method string getClassName()
 */
trait ResourceRepositoryTrait
{
    /**
     * @var string
     */
    private $cachePrefix;


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
     * @param mixed    $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     *
     * @return null|object
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if ($lockVersion && $this->_em) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $this->_em->find($this->_entityName, $id, $lockMode, $lockVersion);
        }

        $query = $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias() . '.id = ' . intval($id))
            ->getQuery();

        if ($lockMode) {
            $query->setLockMode($lockMode);
        }

        return $query->getOneOrNullResult();
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
            ->getResult();
    }

    /**
     * Finds one resource by criteria and sorting.
     *
     * @param array $criteria
     * @param array $orderBy
     *
     * @return null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $qb = $this->getQueryBuilder();

        $this->applyCriteria($qb, $criteria);
        $this->applySorting($qb, $orderBy);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->getCollectionQueryBuilder();

        $this->applyCriteria($qb, $criteria);
        $this->applySorting($qb, $orderBy);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        $query = $qb->getQuery();

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
        $qb = $this->getQueryBuilder();

        $this->applyCriteria($qb, $criteria);

        return $qb
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
            throw new InvalidArgumentException('Please use `findRandomOneBy()` for single result.');
        }

        $qb = $this->getCollectionQueryBuilder();

        $this->applyCriteria($qb, $criteria);

        $query = $qb
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults($limit)
            ->getQuery();

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
    public function createPager(array $criteria = [], array $sorting = null)
    {
        $qb = $this->getCollectionQueryBuilder();

        $this->applyCriteria($qb, $criteria);
        $this->applySorting($qb, $sorting);

        return $this->getPager($qb);
    }

    /**
     * Returns the (doctrine) pager.
     *
     * @param Query|QueryBuilder $query
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
     * Creates a query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null)
    {
        $alias = $alias ?: $this->getAlias();

        /** @noinspection PhpUndefinedClassInspection */
        return parent::createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the singe result query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder($alias = null, $indexBy = null)
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the collection query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return QueryBuilder
     */
    protected function getCollectionQueryBuilder($alias = null, $indexBy = null)
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Applies the criteria to the query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $qb, array $criteria = [])
    {
        foreach ($criteria as $property => $value) {
            $name = $this->getPropertyName($property);
            if (null === $value) {
                $qb->andWhere($qb->expr()->isNull($name));
            } elseif (is_array($value)) {
                $qb->andWhere($qb->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $qb
                    ->andWhere($qb->expr()->eq($name, ':' . $parameter))
                    ->setParameter($parameter, $value);
            }
        }
    }

    /**
     * Applies the sorting to the query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $qb, array $sorting = null)
    {
        if (empty($sorting)) {
            return;
        }

        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $qb->addOrderBy($this->getPropertyName($property), $order);
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
            return $this->getAlias() . '.' . $name;
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
     * Returns the cache prefix.
     *
     * @return string
     */
    public function getCachePrefix()
    {
        if ($this->cachePrefix) {
            return $this->cachePrefix;
        }

        $class = $this->getClassName();

        if (!in_array(TaggedEntityInterface::class, class_implements($class))) {
            throw new \RuntimeException(sprintf(
                'The entity %s does not implements %s, query should not be cached.',
                $class, TaggedEntityInterface::class
            ));
        }

        return $this->cachePrefix = call_user_func([$this->getClassName(), 'getEntityTagPrefix']);
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

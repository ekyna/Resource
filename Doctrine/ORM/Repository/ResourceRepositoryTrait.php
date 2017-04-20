<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

use function is_subclass_of;

/**
 * Trait ResourceRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
trait ResourceRepositoryTrait
{
    protected EntityRepository $wrapped;
    private ?string            $cachePrefix = null;


    /**
     * Sets the wrapped repository.
     *
     * @param EntityRepository $wrapped
     */
    public function setWrapped(EntityRepository $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func([$this->wrapped, $name], ...$arguments);
    }

    /**
     * Finds the resource by its ID.
     *
     * @param int $id
     *
     * @return ResourceInterface|null
     * @psalm-return ?T
     */
    public function find(int $id): ?ResourceInterface
    {
        $query = $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias() . '.id = ' . $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Finds all resources.
     *
     * @return array<ResourceInterface>|Paginator
     * @psalm-return [T]
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
     * @param array $sorting
     *
     * @return ResourceInterface|null
     * @psalm-return ?T
     */
    public function findOneBy(array $criteria, array $sorting = []): ?ResourceInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $queryBuilder
            //->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @param array    $criteria
     * @param array    $sorting
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<ResourceInterface>|Paginator
     * @psalm-return [T]
     */
    public function findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null)
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
     * @TODO Remove
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
            ->getOneOrNullResult();
    }

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return array|Paginator
     * @TODO Remove
     */
    public function findRandomBy(array $criteria, int $limit)
    {
        $limit = intval($limit);
        if ($limit <= 1) {
            throw new InvalidArgumentException('Please use `findRandomOneBy()` for single result.');
        }

        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        $query = $queryBuilder
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
    public function createPager(array $criteria = [], array $sorting = []): Pagerfanta
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPager($queryBuilder);
    }

    /**
     * Returns the (doctrine) pager.
     *
     * @param Query|QueryBuilder $query
     *
     * @return Pagerfanta
     */
    public function getPager($query): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter($query, true, false));

        return $pager->setNormalizeOutOfRangePages(true);
    }

    /**
     * Returns the (array) pager.
     *
     * @param array $objects
     *
     * @return Pagerfanta
     */
    public function getArrayPager(array $objects): Pagerfanta
    {
        $pager = new Pagerfanta(new ArrayAdapter($objects));

        return $pager->setNormalizeOutOfRangePages(true);
    }

    /**
     * Creates a query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        $alias = $alias ?: $this->getAlias();

        return $this->wrapped->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the resource class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->wrapped->getClassName();
    }

    /**
     * Returns the singe result query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     * @TODO Remove (?)
     */
    protected function getQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the collection query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     * @TODO Remove (?)
     */
    protected function getCollectionQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Applies the criteria to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = []): void
    {
        foreach ($criteria as $property => $value) {
            $name = $this->getPropertyName($property);

            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
                continue;
            }

            if (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
                continue;
            }

            $parameter = str_replace('.', '_', $property);
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq($name, ':' . $parameter))
                ->setParameter($parameter, $value);
        }
    }

    /**
     * Applies the sorting to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = []): void
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
    protected function getPropertyName(string $name): string
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
     * @return array|Paginator
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
    public function getCachePrefix(): string
    {
        if ($this->cachePrefix) {
            return $this->cachePrefix;
        }

        $class = $this->getClassName();

        if (!is_subclass_of($class, TaggedEntityInterface::class)) {
            throw new RuntimeException(sprintf(
                'The entity %s does not implements %s, query should not be cached.',
                $class, TaggedEntityInterface::class
            ));
        }

        return $this->cachePrefix = call_user_func([
            $this->getClassName(),
            'getEntityTagPrefix',
        ]); // TODO getCachePrefix
    }

    /**
     * Returns the alias.
     *
     * @return string
     */
    protected function getAlias(): string
    {
        return 'o';
    }
}

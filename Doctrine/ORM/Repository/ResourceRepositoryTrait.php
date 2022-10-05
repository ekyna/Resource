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

use function call_user_func_array;
use function is_subclass_of;

/**
 * Trait ResourceRepositoryTrait
 * @package  Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author   Étienne Dauvergne <contact@ekyna.com>
 *
 * @template R of ResourceInterface
 */
trait ResourceRepositoryTrait
{
    /** @var EntityRepository<R> */
    protected EntityRepository $wrapped;
    private ?string            $cachePrefix = null;

    /**
     * Sets the wrapped repository.
     */
    public function setWrapped(EntityRepository $wrapped): void
    {
        $this->wrapped = $wrapped;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->wrapped, $name], $arguments);
    }

    /**
     * Finds the resource by its ID.
     *
     * @return R|null
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
     * @return array<R>|Paginator<R>
     */
    public function findAll(): array|Paginator
    {
        return $this
            ->getCollectionQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds one resource by criteria and sorting.
     *
     * @return R|null
     */
    public function findOneBy(array $criteria, array $sorting = []): ?ResourceInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @return array<R>|Paginator<R>
     */
    public function findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null): array|Paginator
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
     * @deprecated
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
     * @deprecated
     */
    public function findRandomBy(array $criteria, int $limit)
    {
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
     * Creates a paginator.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return Paginator
     */
    public function createPager(array $criteria = [], array $sorting = []): Paginator
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPager($queryBuilder);
    }

    /**
     * Returns the (doctrine) paginator.
     */
    public function getPager(Query|QueryBuilder $query): Paginator
    {
        return new Paginator($query, true);
    }

    /**
     * Creates a query builder.
     */
    protected function createQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        $alias = $alias ?: $this->getAlias();

        return $this->wrapped->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the resource class name.
     */
    public function getClassName(): string
    {
        return $this->wrapped->getClassName();
    }

    /**
     * Returns the singe result query builder.
     */
    protected function getQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Returns the collection query builder.
     */
    protected function getCollectionQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * Applies the criteria to the query builder.
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
     */
    protected function getPropertyName(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $this->getAlias() . '.' . $name;
        }

        return $name;
    }

    /**
     * Returns the collection results.
     *
     * @return array<R>|Paginator<R>
     */
    protected function collectionResult(Query $query): array|Paginator
    {
        return new Paginator($query, true);
    }

    /**
     * Returns the cache prefix.
     *
     * @return string
     *
     * @TODO Remove
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
     */
    protected function getAlias(): string
    {
        return 'o';
    }
}

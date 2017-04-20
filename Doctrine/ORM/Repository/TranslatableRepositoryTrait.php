<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Class TranslatableRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableRepositoryTrait
{
    use LocaleAwareRepositoryTrait;
    use ResourceRepositoryTrait {
        getQueryBuilder as traitGetQueryBuilder;
        getCollectionQueryBuilder as traitGetCollectionQueryBuilder;
        getPropertyName as traitGetPropertyName;
    }

    protected array $translatableFields = [];


    /**
     * Returns the singe result query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        $alias = $alias ?: $this->getAlias();

        $qb = $this->traitGetQueryBuilder($alias, $indexBy);

        return $qb
            ->addSelect('translation')
            ->leftJoin($alias . '.translations', 'translation');
    }

    /**
     * Returns the collection query builder.
     *
     * @param string|null $alias
     * @param string|null $indexBy
     *
     * @return QueryBuilder
     */
    protected function getCollectionQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
    {
        $alias = $alias ?: $this->getAlias();

        $qb = $this->traitGetCollectionQueryBuilder($alias, $indexBy);

        return $qb
            ->addSelect('translation')
            ->leftJoin($alias . '.translations', 'translation');
    }

    /**
     * Sets the translatable fields.
     *
     * @param array $fields
     *
     * @return $this|TranslatableRepositoryInterface
     */
    public function setTranslatableFields(array $fields): TranslatableRepositoryInterface
    {
        $this->translatableFields = $fields;

        return $this;
    }

    /**
     * Returns the aliased property name (for query builder usage).
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName(string $name): string
    {
        if (in_array($name, $this->translatableFields)) {
            return 'translation.' . $name;
        }

        return $this->traitGetPropertyName($name);
    }

    /**
     * Returns the paginated collection result.
     *
     * @param Query $query
     *
     * @return array|Paginator
     */
    protected function collectionResult(Query $query)
    {
        return new Paginator($query, true);
    }
}

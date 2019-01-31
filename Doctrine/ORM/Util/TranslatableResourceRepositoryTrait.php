<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Class TranslatableResourceRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Util
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableResourceRepositoryTrait
{
    use LocaleAwareRepositoryTrait;
    use ResourceRepositoryTrait {
        createNew as traitCreateNew;
        getQueryBuilder as traitGetQueryBuilder;
        getCollectionQueryBuilder as traitGetCollectionQueryBuilder;
        getPropertyName as traitGetPropertyName;
    }

    /**
     * @var array
     */
    protected $translatableFields = [];


    /**
     * Returns the singe result query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder($alias = null, $indexBy = null)
    {
        $qb = $this->traitGetQueryBuilder($alias, $indexBy);

        $alias = $alias ?: $this->getAlias();

        return $qb
            ->addSelect('translation')
            ->leftJoin($alias . '.translations', 'translation');
    }

    /**
     * Returns the collection query builder.
     *
     * @param string $alias
     * @param string $indexBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getCollectionQueryBuilder($alias = null, $indexBy = null)
    {
        $qb = $this->traitGetCollectionQueryBuilder($alias, $indexBy);

        $alias = $alias ?: $this->getAlias();

        return $qb
            ->addSelect('translation')
            ->leftJoin($alias . '.translations', 'translation');
    }

    /**
     * Returns a new resource instance.
     *
     * @throws \InvalidArgumentException
     *
     * @return object
     */
    public function createNew()
    {
        $resource = $this->traitCreateNew();

        if (!$resource instanceof TranslatableInterface) {
            throw new \InvalidArgumentException('Resource must implement TranslatableInterface.');
        }

        $resource->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $resource->setFallbackLocale($this->localeProvider->getFallbackLocale());

        return $resource;
    }

    /**
     * Sets the translatable fields.
     *
     * @param array $translatableFields
     *
     * @return $this
     */
    public function setTranslatableFields(array $translatableFields)
    {
        $this->translatableFields = $translatableFields;

        return $this;
    }

    /**
     * Returns the aliased property name (for query builder usage).
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName($name)
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
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function collectionResult(Query $query)
    {
        return new Paginator($query, true);
    }
}

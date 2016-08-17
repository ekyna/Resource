<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Class TranslatableResourceRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableResourceRepositoryTrait
{
    use ResourceRepositoryTrait {
        createNew as traitCreateNew;
        getQueryBuilder as traitGetQueryBuilder;
        getCollectionQueryBuilder as traitGetCollectionQueryBuilder;
        getPropertyName as traitGetPropertyName;
    }

    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var array
     */
    protected $translatableFields = [];


    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder()
    {
        $qb = $this->traitGetQueryBuilder();

        $qb
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
        ;

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getCollectionQueryBuilder()
    {
        $qb = $this->traitGetCollectionQueryBuilder();

        $qb
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
        ;

        return $qb;
    }

    /**
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
     * @param LocaleProviderInterface $provider
     *
     * @return $this
     */
    public function setLocaleProvider(LocaleProviderInterface $provider)
    {
        $this->localeProvider = $provider;

        return $this;
    }

    /**
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
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName($name)
    {
        if (in_array($name, $this->translatableFields)) {
            return 'translation.'.$name;
        }
        return $this->traitGetPropertyName($name);
    }

    /**
     * @param Query $query
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function collectionResult(Query $query)
    {
        return new Paginator($query, true);
    }
}

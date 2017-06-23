<?php

namespace Ekyna\Component\Resource\Search\Elastica;

use Ekyna\Component\Resource\Search\SearchRepositoryInterface;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;

/**
 * Class ResourceRepository
 * @package Ekyna\Component\Resource\Search\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository extends Repository implements SearchRepositoryInterface
{
    /**
     * Search resources by expression.
     *
     * @param string  $expression
     * @param integer $limit
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    public function defaultSearch($expression, $limit = 10)
    {
        return $this->find($this->createMatchQuery($expression), $limit);
    }

    /**
     * Creates the match query.
     *
     * @param string $expression
     * @param array  $fields
     *
     * @return Query\AbstractQuery
     */
    protected function createMatchQuery($expression, array $fields = [])
    {
        if (0 == strlen($expression)) {
            return new Query\MatchAll();
        }

        if (empty($fields)) {
            $fields = $this->getDefaultMatchFields();
        }

        $query = new Query\MultiMatch();
        $query
            ->setQuery($expression)
            ->setFields($fields);

        return $query;
    }

    /**
     * Returns the default match fields.
     *
     * @return array
     */
    protected function getDefaultMatchFields()
    {
        return ['text'];
    }
}

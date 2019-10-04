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
     * @inheritDoc
     */
    public function defaultSearch(string $expression, int $limit = 10, int $page = 0): array
    {
        $query = $this
            ->createMatchQuery($expression)
            ->setFrom($limit * $page)
            ->setSize($limit);

        return $this->find($query);
    }

    /**
     * @inheritDoc
     */
    public function createMatchQuery(string $expression, array $fields = []): Query
    {
        if (0 == strlen($expression)) {
            return Query::create(new Query\MatchAll());
        }

        if (empty($fields)) {
            $fields = $this->getDefaultMatchFields();
        }

        $query = new Query\MultiMatch();
        $query
            ->setQuery($expression)
            ->setFields($fields);

        if (0 < count($fields)) {
            $query->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
        }

        return Query::create($query);
    }

    /**
     * Returns the default match fields.
     *
     * @return array
     */
    protected function getDefaultMatchFields(): array
    {
        return ['text'];
    }
}

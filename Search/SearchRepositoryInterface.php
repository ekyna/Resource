<?php

namespace Ekyna\Component\Resource\Search;

use Elastica\Query;

/**
 * Interface SearchRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SearchRepositoryInterface
{
    /**
     * Default text search.
     *
     * @param string $expression
     * @param int    $limit
     * @param int    $page
     *
     * @return \Ekyna\Component\Resource\Model\ResourceInterface[]
     */
    public function defaultSearch(string $expression, int $limit = 10, int $page = 0): array;

    /**
     * Creates the match query.
     *
     * @param string $expression
     * @param array  $fields
     *
     * @return Query
     */
    public function createMatchQuery(string $expression, array $fields = []): Query;
}

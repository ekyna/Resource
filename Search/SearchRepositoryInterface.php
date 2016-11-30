<?php

namespace Ekyna\Component\Resource\Search;

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
     * @param string  $expression
     * @param integer $limit
     *
     * @return array
     */
    public function defaultSearch($expression, $limit = 10);
}

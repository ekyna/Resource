<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Search;

/**
 * Interface SearchRepositoryFactoryInterface
 * @package Ekyna\Component\Resource\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SearchRepositoryFactoryInterface
{
    /**
     * Returns the resource search repository for the given resource class.
     *
     * @param string $name The resource class.
     *
     * @return SearchRepositoryInterface
     */
    public function getRepository(string $name): SearchRepositoryInterface;
}

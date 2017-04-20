<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Search;

/**
 * Interface SearchRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SearchRepositoryInterface
{
    /**
     * Returns the results for the given search request.
     *
     * @param Request $request
     *
     * @return array
     */
    public function search(Request $request): array;

    /**
     * Returns whether the given search request is supported.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool;
}

<?php

namespace Ekyna\Component\Resource\Search;

/**
 * Class Search
 * @package Ekyna\Bundle\CmsBundle\Search\Wide
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Search
{
    /**
     * @var ResourceRepositoryInterface[]
     */
    private $repositories = [];


    /**
     * Adds the repository.
     *
     * @param string                      $name
     * @param ResourceRepositoryInterface $repository
     *
     * @return Search
     */
    public function addRepository(string $name, ResourceRepositoryInterface $repository): self
    {
        if (array_key_exists($name, $this->repositories)) {
            throw new \InvalidArgumentException("Search repository '$name' is already registered.");
        }

        $this->repositories[$name] = $repository;

        return $this;
    }

    /**
     * Returns the repositories.
     *
     * @return ResourceRepositoryInterface[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * Searches across all resources (having a search provider).
     *
     * @param Request $request
     *
     * @return array|Result[]
     */
    public function search(Request $request): array
    {
        if (empty($request->getExpression())) {
            return [];
        }

        $filter = !empty($resources = $request->getResources());

        $results = [];

        foreach ($this->repositories as $name => $repository) {
            if ($filter && !in_array($name, $resources, true)) {
                continue;
            }

            if (!$repository->supports($request)) {
                continue;
            }

            $results = array_merge($results, $repository->search($request));
        }

        if ($request->getType() === Request::RAW) {
            // TODO
            return $results;
        }

        if ($request->getType() === Request::RESOURCE) {
            // TODO
            return $results;
        }

        usort($results, function (Result $a, Result $b) {
            if ($a->getScore() == $b->getScore()) {
                return 0;
            }

            return $a->getScore() > $b->getScore() ? -1 : 1;
        });

        return array_slice($results, 0, $request->getLimit());
    }
}

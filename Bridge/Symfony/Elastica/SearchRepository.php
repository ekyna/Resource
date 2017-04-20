<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Elastica;

use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;
use Ekyna\Component\Resource\Search\SearchRepositoryInterface;
use Elastica\Query;
use Elastica\Result as ElasticaResult;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

use function array_map;
use function is_array;
use function is_object;

/**
 * Class SearchRepository
 * @package Ekyna\Component\Resource\Bridge\Symfony\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SearchRepository implements SearchRepositoryInterface
{
    protected SearchableInterface                 $searchable;
    protected ElasticaToModelTransformerInterface $transformer;


    public function setSearchable(SearchableInterface $searchable): void
    {
        $this->searchable = $searchable;
    }

    public function setTransformer(ElasticaToModelTransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }

    public function search(Request $request): array
    {
        if (!$this->supports($request)) {
            throw new RuntimeException('Unsupported search request.');
        }

        if (empty($request->getExpression())) {
            return [];
        }

        $query = Query::create($this->createQuery($request));
        $query
            ->setSize($request->getLimit())
            ->setFrom($request->getOffset());

        $results = $this->searchable->search($query);

        if ($request->getType() === Request::RESULT) {
            if ($this->needsTransformedSource($request)) {
                $results = $this->transformer->hybridTransform($results->getResults());

                $builder = function (HybridResult $data, &$return) use ($request) {
                    if (!$result = $this->createResult($data->getTransformed(), $request)) {
                        return;
                    }

                    $return[] = $result->setScore($data->getResult()->getScore());
                };
            } else {
                $builder = function (ElasticaResult $data, &$return) use ($request) {
                    if (!$result = $this->createResult($data->getSource(), $request)) {
                        return;
                    }

                    $return[] = $result->setScore($data->getScore());
                };
            }

            $return = [];
            foreach ($results as $data) {
                $builder($data, $return);
            }

            return $return;
        }

        if ($request->getType() === Request::RESOURCE) {
            return $this->transformer->transform($results->getResults());
        }

        return [
            'results'     => array_map(function (ElasticaResult $result) {
                return $result->getSource();
            }, $results->getResults()),
            'total_count' => $results->getTotalHits(),
        ];
    }

    public function supports(Request $request): bool
    {
        return $request->isPrivate()
            && !empty($request->getExpression())
            && 0 < $request->getLimit();
    }

    /**
     * Creates the result.
     *
     * @param array|object $source
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$request->isPrivate()) {
            return null;
        }

        $title = null;
        if (is_array($source) && isset($source['text'])) {
            $title = $source['text'];
        } elseif (is_object($source)) {
            $title = (string)$source;
        }

        if (empty($title)) {
            return null;
        }

        $result = new Result();
        $result->setTitle($title);

        return $result;
    }

    /**
     * Returns whether transformed source is needed to create results.
     */
    protected function needsTransformedSource(Request $request): bool
    {
        return false;
    }

    /**
     * Creates the search query.
     */
    protected function createQuery(Request $request): Query\AbstractQuery
    {
        if (empty($request->getExpression())) {
            return new Query\MatchAll();
        }

        if (empty($fields = $request->getFields())) {
            $fields = $this->getDefaultFields();
        }

        $query = new Query\MultiMatch();
        $query
            ->setQuery($request->getExpression())
            ->setFields($fields);

        if (!empty($fields)) {
            $query->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
        }

        return $query;
    }

    /**
     * Returns the default search fields.
     */
    protected function getDefaultFields(): array
    {
        return ['text'];
    }
}

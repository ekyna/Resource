<?php

namespace Ekyna\Component\Resource\Search\Elastica;

use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Search\Result;
use Elastica\Query;
use Elastica\Result as ElasticaResult;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

/**
 * Class ResourceRepository
 * @package Ekyna\Component\Resource\Search\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @var ElasticaToModelTransformerInterface
     */
    private $transformer;


    /**
     * Constructor.
     *
     * @param SearchableInterface                 $searchable
     * @param ElasticaToModelTransformerInterface $transformer
     */
    public function __construct(
        SearchableInterface $searchable,
        ElasticaToModelTransformerInterface $transformer
    ) {
        $this->searchable  = $searchable;
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function search(Request $request): array
    {
        if (!$this->supports($request)) {
            throw new RuntimeException("Unsupported search request.");
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

    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return $request->isPrivate() && !empty($request->getExpression()) && 0 < $request->getLimit();
    }

    /**
     * Creates the result.
     *
     * @param array|object $source
     * @param Request      $request
     *
     * @return Result|null
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
     *
     * @param Request $request
     *
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    protected function needsTransformedSource(Request $request): bool
    {
        return false;
    }

    /**
     * Creates the search query.
     *
     * @param Request $request
     *
     * @return Query\AbstractQuery
     */
    protected function createQuery(Request $request): Query\AbstractQuery
    {
        if (0 == strlen($request->getExpression())) {
            return new Query\MatchAll();
        }

        if (empty($fields = $request->getFields())) {
            $fields = $this->getDefaultFields();
        }

        $query = new Query\MultiMatch();
        $query
            ->setQuery($request->getExpression())
            ->setFields($fields);

        if (0 < count($fields)) {
            $query->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
        }

        return $query;
    }

    /**
     * Returns the default search fields.
     *
     * @return array
     */
    protected function getDefaultFields(): array
    {
        return ['text'];
    }
}

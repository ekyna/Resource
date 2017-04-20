<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Search;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;

use function array_map;
use function in_array;

/**
 * Class Request
 * @package Ekyna\Bundle\ResourceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Request
{
    public const RESOURCE = 'resource';
    public const RESULT   = 'result';
    public const RAW      = 'raw';

    private string  $type;
    private ?string $expression;
    private array   $resources;
    private array   $fields;
    private array   $parameters;
    private bool    $private;
    private int     $limit;
    private int     $offset;


    /**
     * Constructor.
     *
     * @param string|null $expression
     */
    public function __construct(string $expression = null)
    {
        $this->type = self::RESULT;
        $this->expression = $expression;
        $this->resources = [];
        $this->fields = [];
        $this->parameters = [];
        $this->private = false;
        $this->limit = 10;
        $this->offset = 0;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return Request
     */
    public function setType(string $type): self
    {
        if (!in_array($type, [self::RESOURCE, self::RESULT, self::RAW], true)) {
            throw new InvalidArgumentException('Unexpected request type.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the expression.
     *
     * @return string
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * Sets the expression.
     *
     * @param string|null $expression
     *
     * @return Request
     */
    public function setExpression(string $expression = null): self
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Returns the resources.
     *
     * @return string[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Sets the resources.
     *
     * @param string[] $resources
     *
     * @return Request
     */
    public function setResources(array $resources): self
    {
        $this->resources = array_map(function ($resource) {
            return (string)$resource;
        }, $resources);

        return $this;
    }

    /**
     * Returns the fields.
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Sets the fields.
     *
     * @param string[] $fields
     *
     * @return Request
     */
    public function setFields(array $fields): self
    {
        $this->fields = array_map(function ($field) {
            return (string)$field;
        }, $fields);

        return $this;
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns whether the parameter exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Returns the parameter value.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getParameter(string $key, $default = null)
    {
        if ($this->hasParameter($key)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * Sets the parameter.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Request
     */
    public function setParameter(string $key, $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Sets the parameters.
     *
     * @param array $parameters
     *
     * @return Request
     */
    public function setParameters(array $parameters): self
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * Returns the private.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * Sets the private.
     *
     * @param bool $private
     *
     * @return Request
     */
    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Returns the limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Sets the limit.
     *
     * @param int $limit
     *
     * @return Request
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Returns the offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Sets the offset.
     *
     * @param int $offset
     *
     * @return Request
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }
}

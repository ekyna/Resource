<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_key_exists;
use function array_map;
use function implode;
use function iterator_to_array;
use function sprintf;
use function trim;

/**
 * Class AbstractConsumer
 * @package Ekyna\Component\Commerce\Common\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConsumer implements ConsumerInterface
{
    protected ConfigInterface            $config;
    protected FactoryFactoryInterface    $factoryFactory;
    protected RepositoryFactoryInterface $repositoryFactory;
    protected ValidatorInterface         $validator;
    protected PropertyAccessor           $accessor;

    protected ?ResourceInterface $resource = null;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function setConfig(ConfigInterface $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function getResource(): ?ResourceInterface
    {
        return $this->resource;
    }

    public function initialize(
        FactoryFactoryInterface    $factoryFactory,
        RepositoryFactoryInterface $repositoryFactory,
        ValidatorInterface         $validator,
        PropertyAccessor           $accessor
    ) {
        $this->factoryFactory = $factoryFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->validator = $validator;
        $this->accessor = $accessor;
    }

    public function consume(array $data): ?ResourceInterface
    {
        if (null === $this->resource = $this->createResource()) {
            return null;
        }

        $properties = $this->config::getKeys();
        $numbers = $this->config->getNumbers();

        foreach ($properties as $property) {
            if (!isset($numbers[$property])) {
                continue;
            }

            $value = $this->readColumn($data, $numbers[$property] - 1);

            if (null === $value = $this->transformValue($property, $value)) {
                continue;
            }

            $this->writeValue($property, $value);
        }

        if (!$this->validateResource()) {
            $this->resource = null;
        }

        return $this->resource;
    }

    public function preFlush(): void
    {
    }

    public function postFlush(): void
    {
    }


    abstract protected function createResource(): ?ResourceInterface;

    /**
     * @return mixed
     */
    protected function readColumn(array $data, int $column)
    {
        if (!array_key_exists($column, $data)) {
            throw new ImportException("No data at column $column");
        }

        return trim((string)$data[$column]);
    }

    /**
     * @return mixed
     */
    protected function transformValue(string $property, string $value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     */
    protected function writeValue(string $property, $value): void
    {
        $this->accessor->setValue($this->resource, $property, $value);
    }

    protected function validateResource(): bool
    {
        $violations = $this->validator->validate($this->resource);

        if (0 === $violations->count()) {
            return true;
        }

        $message = implode(". \n", array_map(function (ConstraintViolationInterface $violation) {
            if ($path = $violation->getPropertyPath()) {
                return sprintf('[%s] (%s) %s', $path, $violation->getInvalidValue(), $violation->getMessage());
            }

            return sprintf('(%s) %s', $violation->getInvalidValue(), $violation->getMessage());
        }, iterator_to_array($violations)));

        throw new ImportException($message);
    }
}

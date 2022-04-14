<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function fgetcsv;
use function fopen;

use const INF;

/**
 * Class CsvImporter
 * @package Ekyna\Component\Resource\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class CsvImporter
{
    protected FactoryFactoryInterface    $factoryFactory;
    protected RepositoryFactoryInterface $repositoryFactory;
    protected ValidatorInterface         $validator;
    protected EntityManagerInterface     $manager;
    protected PropertyAccessor           $accessor;

    protected ImportConfig $config;

    /** @var array<string> */
    private array $errors = [];
    /** @var array<ResourceInterface> */
    private array $resources = [];

    public function __construct(
        FactoryFactoryInterface    $factoryFactory,
        RepositoryFactoryInterface $repositoryFactory,
        ValidatorInterface         $validator,
        EntityManagerInterface     $manager
    ) {
        $this->factoryFactory = $factoryFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->validator = $validator;
        $this->manager = $manager;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Imports the resources from the given config.
     *
     * @return int The imported resources count.
     */
    public function import(ImportConfig $config): int
    {
        $this->config = $config;
        $this->errors = [];
        $this->resources = [];

        if (false === $handle = fopen($path = $config->getPath(), 'r')) {
            throw new ImportException("Failed to open file $path.");
        }

        foreach ($config->getConsumers() as $consumer) {
            $consumer->initialize(
                $this->factoryFactory,
                $this->repositoryFactory,
                $this->validator,
                $this->accessor
            );
        }

        $from = $config->getFrom() ?? 1;
        $to = $config->getTo() ?? INF;

        $line = $count = 0;
        while ($data = fgetcsv($handle, 2048, $config->getSeparator(), $config->getEnclosure())) {
            $line++;

            if ($line < $from) {
                continue;
            }

            if ($line > $to) {
                continue;
            }

            $group = [];
            foreach ($config->getConsumers() as $consumer) {
                try {
                    if (null === $resource = $consumer->consume($data)) {
                        continue;
                    }
                } catch (ImportException $exception) {
                    $this->errors[] = "Line $line: " . $exception->getMessage();

                    if (10 < count($this->errors)) {
                        break 2;
                    }

                    continue 2;
                }

                if ($resource) {
                    $group[] = $resource;
                    $count++;
                }
            }

            if (empty($group)) {
                continue;
            }

            $this->resources[] = $group;
        }

        if (!empty($this->errors)) {
            $this->resources = [];

            return 0;
        }

        return $count;
    }

    /**
     * Returns the errors.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Persists the imported resources.
     */
    public function flush(): void
    {
        if (empty($resources = $this->resources)) {
            return;
        }

        $this->resources = [];

        $this->preFlush();

        $count = 0;
        foreach ($resources as $group) {
            $count++;
            foreach ($group as $resource) {
                $this->persist($resource);
            }

            if (0 === $count % 10) {
                $this->manager->flush();
            }
        }

        if (0 !== $count % 10) {
            $this->manager->flush();
        }

        $this->postFlush();
    }

    protected function persist(ResourceInterface $resource): void
    {
        $this->manager->persist($resource);
    }

    protected function preFlush(): void
    {
        foreach ($this->config->getConsumers() as $consumer) {
            $consumer->preFlush();
        }
    }

    protected function postFlush(): void
    {
        foreach ($this->config->getConsumers() as $consumer) {
            $consumer->postFlush();
        }
    }
}

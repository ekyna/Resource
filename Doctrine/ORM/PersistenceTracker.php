<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

use function array_key_exists;
use function get_class;
use function spl_object_hash;
use function sprintf;

/**
 * Class PersistenceTracker
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This is a workaround for https://github.com/doctrine/doctrine2/issues/5198
 *
 * @TODO    Check Doctrine\Common\PropertyChangedListener
 */
class PersistenceTracker implements PersistenceTrackerInterface
{
    protected ManagerRegistry $registry;
    /** @var array<Tracking\NormalizerInterface> */
    protected array $converters = [];

    protected array $originalData;
    protected array $changeSets;

    public function __construct(ManagerRegistry $registry, array $converters = [])
    {
        $this->registry = $registry;

        $this->registerConverter(new Tracking\DateTimeNormalizer());
        $this->registerConverter(new Tracking\DecimalNormalizer());

        foreach ($converters as $converter) {
            $this->registerConverter($converter);
        }

        $this->clear();
    }

    public function registerConverter(Tracking\NormalizerInterface $converter): void
    {
        foreach ($converter->getTypes() as $type) {
            if (isset($this->converters[$type])) {
                throw new RuntimeException(sprintf('A converter is already registered for type \'%s\'.', $type));
            }

            $this->converters[$type] = $converter;
        }
    }

    public function computeChangeSet(ResourceInterface $resource): void
    {
        $class = get_class($resource);

        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass($class);

        $metadata = $manager->getClassMetadata($class);

        $oid = spl_object_hash($resource);

        if (!isset($this->originalData[$oid])) {
            $originalData = [];
            $uow = $manager->getUnitOfWork();

            // For new entities, the original data returned by the doctrine UOW
            // reflects the persisted values and not the true original data.
            // Overrides the original data with null values.
            if ($uow->isScheduledForInsert($resource)) {
                foreach ($metadata->reflFields as $name => $refProp) {
                    if ($this->isBasicOrSingleAssociation($name, $metadata)) {
                        $originalData[$name] = null;
                    }
                }
            }
            // Entity has been fetched from database. Build original data by
            // overriding the UOW original data with the UOW change set.
            // TODO Only overridden data will be correct (i.e. really the original one),
            // we may use a postLoad event but we don't want to store all entities original data :s ...
            else {
                $originalData = $uow->getOriginalEntityData($resource);
                foreach ($metadata->reflFields as $name => $refProp) {
                    if (isset($originalData[$name]) || !$this->isSingleAssociation($name, $metadata)) {
                        continue;
                    }

                    // Retrieve original object for cleared *ToOne association.
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $column = $metadata->getSingleAssociationJoinColumnName($name);
                    if (!isset($originalData[$column])) {
                        continue;
                    }

                    $object = $uow->tryGetById(
                        $originalData[$column],
                        $metadata->getAssociationTargetClass($name)
                    );
                    if (false !== $object) {
                        $originalData[$name] = $object;
                    }
                }

                // Override with uow change set.
                $changeSet = $uow->getEntityChangeSet($resource);
                foreach ($changeSet as $name => $data) {
                    $originalData[$name] = $data[0];
                }
            }

            $this->originalData[$oid] = $originalData;
        } else {
            $originalData = $this->originalData[$oid];
        }

        $actualData = [];
        foreach ($metadata->reflFields as $name => $refProp) {
            if (!$this->isBasicOrSingleAssociation($name, $metadata)) {
                continue;
            }

            $actualData[$name] = $refProp->getValue($resource);
        }

        $changeSet = [];
        foreach ($actualData as $name => $actualValue) {
            // Skip embedded entities: changes are stored under keys in the form <name>.<embeddedProperty>.
            if (array_key_exists($name, $metadata->embeddedClasses)) {
                continue;
            }

            $orgValue = $originalData[$name] ?? null;

            if ($this->isDifferent($orgValue, $actualValue, $name, $metadata)) {
                $changeSet[$name] = [$orgValue, $actualValue];
            }
        }

        $this->changeSets[$oid] = $changeSet;
    }

    /**
     * @param object|array|string|float|int|bool $a
     * @param object|array|string|float|int|bool $b
     */
    private function isDifferent($a, $b, string $field, ClassMetadata $metadata): bool
    {
        $a = $this->normalizeData($a, $field, $metadata);
        $b = $this->normalizeData($b, $field, $metadata);

        return $a !== $b;
    }

    /**
     * Returns whether the field is mapped as a basic or single association type.
     */
    private function isBasicOrSingleAssociation(string $field, ClassMetadata $metadata): bool
    {
        return (!$metadata->isIdentifier($field) || !$metadata->isIdGeneratorIdentity())
            && ($field !== $metadata->versionField)
            && !$metadata->isCollectionValuedAssociation($field);
    }

    /**
     * Returns whether the field is mapped as a basic or single association type.
     */
    private function isSingleAssociation(string $field, ClassMetadata $metadata): bool
    {
        return $metadata->isSingleValuedAssociation($field)
            && $metadata->isAssociationWithSingleJoinColumn($field);
    }

    /**
     * Normalizes the data for comparison.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function normalizeData($data, string $field, ClassMetadata $metadata)
    {
        if (!isset($metadata->fieldMappings[$field])) {
            return $data;
        }

        $mapping = $metadata->fieldMappings[$field];

        if (!isset($this->converters[$type = $mapping['type']])) {
            return $data;
        }

        return $this->converters[$type]->convert($data, $mapping);
    }

    public function getChangeSet(ResourceInterface $entity, string $property = null): array
    {
        $oid = spl_object_hash($entity);

        if (!isset($this->changeSets[$oid])) {
            $this->computeChangeSet($entity);
        }

        $changeSet = $this->changeSets[$oid];
        if (null === $property) {
            return $changeSet;
        }

        if (isset($changeSet[$property])) {
            return $changeSet[$property];
        }

        return [];
    }

    public function clearChangeSets(): void
    {
        $this->changeSets = [];
    }

    public function clear(): void
    {
        $this->originalData = [];
        $this->changeSets = [];
    }

    public function postFlush(): void
    {
        $this->clear();
    }
}

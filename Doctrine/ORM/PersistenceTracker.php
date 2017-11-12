<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

/**
 * Class PersistenceTracker
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This is a workaround for https://github.com/doctrine/doctrine2/issues/5198
 *
 * @TODO    Use resource interface
 */
class PersistenceTracker implements PersistenceTrackerInterface
{
    /**
     * // TODO use doctrine registry to get resource own manager (if not default)
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var array
     */
    protected $originalData;

    /**
     * @var array
     */
    protected $changeSets;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;

        $this->originalData = [];

        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function computeChangeSet($entity)
    {
        $class = $this->manager->getClassMetadata(get_class($entity));

        $oid = spl_object_hash($entity);

        if (!isset($this->originalData[$oid])) {
            $originalData = [];
            $uow = $this->manager->getUnitOfWork();

            // For new entities, the original data returned by the doctrine UOW
            // reflects the persisted values and not the true original data.
            // Overrides the original data with null values.
            if ($uow->isScheduledForInsert($entity)) {
                foreach ($class->reflFields as $name => $refProp) {
                    if ($this->isBasicOrSingleAssociation($name, $class)) {
                        $originalData[$name] = null;
                    }
                }
            }
            // Entity has been fetched from database, build original data by
            // overriding the UOW original data with the UOW change set.
            // TODO Only overridden data will be correct (i.e. really the original one),
            // we may use a postLoad event but we don't want to store all entities original data :s ...
            else {
                $originalData = $uow->getOriginalEntityData($entity);
                $changeSet = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $name => $data) {
                    $originalData[$name] = $this->normalizeData($data[0], $name, $class);
                }
            }

            $this->originalData[$oid] = $originalData;
        } else {
            $originalData = $this->originalData[$oid];
        }

        $actualData = [];

        foreach ($class->reflFields as $name => $refProp) {
            if ($this->isBasicOrSingleAssociation($name, $class)) {
                $actualData[$name] = $refProp->getValue($entity);
            }
        }

        $changeSet = [];

        foreach ($actualData as $name => $actualValue) {
            $orgValue = isset($originalData[$name]) ? $originalData[$name] : null;

            if ($orgValue !== $actualValue) {
                $changeSet[$name] = [$orgValue, $actualValue];
            }
        }

        $this->changeSets[$oid] = $changeSet;
    }

    /**
     * Returns whether the field is mapped as a basic or single association type.
     *
     * @param string        $field
     * @param ClassMetadata $class
     *
     * @return bool
     */
    private function isBasicOrSingleAssociation($field, ClassMetadata $class)
    {
        return (!$class->isIdentifier($field) || !$class->isIdGeneratorIdentity())
            && ($field !== $class->versionField)
            && !$class->isCollectionValuedAssociation($field);
    }

    /**
     * Normalizes the data.
     *
     * @param mixed         $data
     * @param string        $field
     * @param ClassMetadata $class
     *
     * @return mixed
     */
    private function normalizeData($data, $field, ClassMetadata $class)
    {
        if (isset($class->fieldMappings[$field]) && $class->fieldMappings[$field]['type'] === 'decimal') {
            return round((float)$data, $class->fieldMappings[$field]['scale']);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getChangeSet($entity, $property = null)
    {
        $oid = spl_object_hash($entity);

        if (!isset($this->changeSets[$oid])) {
            $this->computeChangeSet($entity);
        }

        $changeSet = $this->changeSets[$oid];
        if (null !== $property) {
            if (isset($changeSet[$property])) {
                return $changeSet[$property];
            }

            return [];
        }

        return $changeSet;
    }

    /**
     * @inheritdoc
     */
    public function clearChangeSets()
    {
        $this->changeSets = [];
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->originalData = [];
        $this->changeSets = [];
    }
}

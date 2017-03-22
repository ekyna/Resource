<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;

/**
 * Class PersistenceTracker
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This is a workaround for https://github.com/doctrine/doctrine2/issues/5198
 *
 * @TODO Use resource interface
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
            if ($uow->isScheduledForInsert($entity)) { // TODO ResourceInterface::getId() ?
                foreach ($class->reflFields as $name => $refProp) {
                    if ((!$class->isIdentifier($name) || !$class->isIdGeneratorIdentity())
                        && ($name !== $class->versionField)
                        && !$class->isCollectionValuedAssociation($name)
                    ) {
                        $originalData[$name] = null;
                    }
                }
            }
            // Entity has been fetched from database, we can trust the UOW
            else {
                $originalData = $uow->getOriginalEntityData($entity);
                $changeSet = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $field => $data) {
                    $originalData[$field] = $data[0];
                }
            }

            $this->originalData[$oid] = $originalData;
        } else {
            $originalData = $this->originalData[$oid];
        }

        $actualData = [];

        foreach ($class->reflFields as $name => $refProp) {
            if ((!$class->isIdentifier($name) || !$class->isIdGeneratorIdentity())
                && ($name !== $class->versionField)
                && !$class->isCollectionValuedAssociation($name)
            ) {
                $actualData[$name] = $refProp->getValue($entity);
            }
        }

        $changeSet = [];

        foreach ($actualData as $propName => $actualValue) {
            $orgValue = isset($originalData[$propName]) ? $originalData[$propName] : null;

            if ($orgValue !== $actualValue) {
                $changeSet[$propName] = [$orgValue, $actualValue];
            }
        }

        $this->changeSets[$oid] = $changeSet;
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

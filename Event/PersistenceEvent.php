<?php

namespace Ekyna\Component\Resource\Event;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PersistenceEvent
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceEvent extends ResourceEvent
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;


    /**
     * Returns the manager.
     *
     * @return EntityManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Sets the manager.
     *
     * @param EntityManagerInterface $manager
     */
    public function setManager(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns the entity change set.
     *
     * @return array
     */
    public function getChangeSet()
    {
        return $this->manager
            ->getUnitOfWork()
            ->getEntityChangeSet($this->resource);
    }

    /**
     * Returns whether at least one one of the properties has changed.
     *
     * @param string|array $properties
     *
     * @return bool
     */
    public function isChanged($properties)
    {
        $changeSet = $this->getChangeSet();

        if (is_string($properties)) {
            return array_key_exists($properties, $changeSet);
        } elseif (is_array($properties)) {
            return 0 < count(array_intersect($properties, array_keys($changeSet)));
        }

        throw new \InvalidArgumentException('Expected string or array.');
    }

    /**
     * Persists and recompute the given entity.
     *
     * @param mixed $entity
     */
    public function persistAndRecompute(ResourceInterface $entity)
    {
        $uow = $this->manager->getUnitOfWork();

        if (!($uow->isScheduledForInsert($entity) || $uow->isScheduledForUpdate($entity))) {
            $uow->persist($entity);
        }

        $metadata = $this->manager->getClassMetadata(get_class($entity));
        $uow->recomputeSingleEntityChangeSet($metadata, $entity);
    }
}

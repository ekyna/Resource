<?php

namespace Ekyna\Component\Resource\Persistence;

/**
 * Interface PersistenceTrackerInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceTrackerInterface
{
    /**
     * Computes the entity change set.
     *
     * @param object $entity
     */
    public function computeChangeSet($entity);

    /**
     * Returns the entity change set, optionally for the given property.
     *
     * @param object $entity
     * @param string $property
     *
     * @return array
     */
    public function getChangeSet($entity, $property = null);

    /**
     * Clears the change sets.
     */
    public function clearChangeSets();

    /**
     * Clears the change sets and the original data.
     *
     * Must be called at the end of flush (queue / doctrine event).
     */
    public function clear();
}

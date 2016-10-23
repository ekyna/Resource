<?php

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PersistenceHelperInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceHelperInterface
{
    /**
     * Returns the manager.
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getManager();

    /**
     * Returns the entity change set.
     *
     * @param ResourceInterface $resource
     *
     * @return array
     */
    public function getChangeSet(ResourceInterface $resource);

    /**
     * Returns whether at least one one of the properties has changed.
     *
     *
     * @param ResourceInterface $resource
     * @param string|array $properties
     *
     * @return bool
     */
    public function isChanged(ResourceInterface $resource, $properties);

    /**
     * Persists and recompute the given resource.
     *
     * @param ResourceInterface $resource
     */
    public function persistAndRecompute(ResourceInterface $resource);

    /**
     * Removes the given resource.
     *
     * @param ResourceInterface $resource
     */
    public function remove(ResourceInterface $resource);
}

<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface TrackAssociationInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TrackAssociationInterface
{
    /**
     * Backup associated entities ids.
     */
    public function takeSnapshot();

    /**
     * Returns the ids of entities added to the given association.
     *
     * @param string $association
     *
     * @return array
     */
    public function getInsertedIds($association);

    /**
     * Returns the ids of entities removed from the given association.
     *
     * @param string $association
     *
     * @return array
     */
    public function getRemovedIds($association);
}
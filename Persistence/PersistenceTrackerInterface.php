<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PersistenceTrackerInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceTrackerInterface
{
    /**
     * Computes the resource change set.
     */
    public function computeChangeSet(ResourceInterface $resource): void;

    /**
     * Returns the entity change set, optionally for the given property(ies).
     */
    public function getChangeSet(ResourceInterface $entity, array|string|null $properties): array;

    /**
     * Clears the change sets.
     */
    public function clearChangeSets(): void;

    /**
     * Clears the change sets and the original data.
     *
     * Must be called at the end of flush (queue / doctrine event).
     */
    public function clear(): void;
}

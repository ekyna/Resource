<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Trait TrackAssociationTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TrackAssociationTrait
{
    /** Associated entities ids. */
    protected ?array $snapshot = null;


    /**
     * Backup associated entities ids.
     */
    public function takeSnapshot(): void
    {
        $this->snapshot = [];

        foreach (static::getAssociationsProperties() as $association) {
            $this->snapshot[$association] = [];

            foreach ($this->{$association} as $entity) {
                /** @var ResourceInterface $entity */
                $this->snapshot[$association][] = $entity->getId();
            }
        }
    }

    /**
     * Returns the ids of entities added to the given association.
     *
     * @param string $association
     *
     * @return array
     */
    public function getInsertedIds(string $association): array
    {
        if (is_null($this->snapshot)) {
            throw new RuntimeException('You must take a snapshot first.');
        }

        $current = [];
        /** @var ResourceInterface $entity */
        foreach ($this->{$association} as $entity) {
            $current[] = $entity->getId();
        }

        return array_diff($current, $this->snapshot[$association]);
    }

    /**
     * Returns the ids of entities removed to the given association.
     *
     * @param string $association
     *
     * @return array
     */
    public function getRemovedIds(string $association): array
    {
        if (is_null($this->snapshot)) {
            throw new RuntimeException('You must take a snapshot first.');
        }

        $current = [];
        /** @var ResourceInterface $entity */
        foreach ($this->{$association} as $entity) {
            $current[] = $entity->getId();
        }

        return array_diff($this->snapshot[$association], $current);
    }

    /**
     * Returns the associations properties.
     *
     * @return string[]
     */
    abstract public static function getAssociationsProperties(): array;

    /**
     * Returns the associated entities ids for the given association.
     *
     * @param string $association
     *
     * @return array
     */
    protected function getAssociationIds(string $association): array
    {
        $ids = [];
        /** @var ResourceInterface $entity */
        foreach ($this->{$association} as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * Asserts that the given association is defined.
     *
     * @param string $association
     */
    protected function assertAssociation(string $association): void
    {
        if (in_array($association, static::getAssociationsProperties(), true)) {
            return;
        }

        throw new InvalidArgumentException("Unknown association '$association'.");
    }
}

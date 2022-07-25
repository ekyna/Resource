<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PersistenceEventQueueInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceEventQueueInterface extends EventQueueInterface
{
    public const DELETE = 'delete';
    public const INSERT = 'insert';
    public const UPDATE = 'update';

    /**
     * Schedules the insert resource event.
     */
    public function scheduleInsert(ResourceInterface $resource): void;

    /**
     * Schedules the update resource event.
     */
    public function scheduleUpdate(ResourceInterface $resource): void;

    /**
     * Schedules the delete resource event.
     */
    public function scheduleDelete(ResourceInterface $resource): void;
}

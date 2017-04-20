<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Manager;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ResourceManagerInterface
 * @package  Ekyna\Component\Resource\Doctrine\ORM
 * @author   Étienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface ResourceManagerInterface
{
    public const DI_TAG = 'ekyna_resource.manager';

    public function configure(string $resourceClass, string $eventPrefix, bool $debug): void;

    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher): void;

    /**
     * Returns the resource metadata.
     *
     * @return ClassMetadata
     */
    public function getMetadata(): ClassMetadata;

    /**
     * Persists the resource.
     *
     * @param ResourceInterface $resource
     *
     * @psalm-param T           $resource
     */
    public function persist(ResourceInterface $resource): void;

    /**
     * Refreshes the resource in the manager.
     *
     * @param ResourceInterface $resource
     *
     * @psalm-param T           $resource
     */
    public function refresh(ResourceInterface $resource): void;

    /**
     * Removes the resource in the manager.
     *
     * @param ResourceInterface $resource
     *
     * @psalm-param T           $resource
     */
    public function remove(ResourceInterface $resource): void;

    /**
     * Clears the manager.
     */
    public function flush(): void;

    /**
     * Clears the manager.
     */
    public function clear(): void;

    /**
     * Persists a single resource using events.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @return ResourceEventInterface
     */
    public function save($resourceOrEvent): ResourceEventInterface;

    /**
     * Creates the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @psalm-param T|ResourceEventInterface           $resource
     *
     * @return ResourceEventInterface
     */
    public function create($resourceOrEvent): ResourceEventInterface;

    /**
     * Updates the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @psalm-param T|ResourceEventInterface           $resource
     *
     * @return ResourceEventInterface
     */
    public function update($resourceOrEvent): ResourceEventInterface;

    /**
     * Deletes the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     * @param bool                                     $hard Whether to bypass deletion prevention.
     *
     * @psalm-param T|ResourceEventInterface           $resource
     *
     * @return ResourceEventInterface
     */
    public function delete($resourceOrEvent, bool $hard = false): ResourceEventInterface;

    /**
     * Creates the resource event.
     *
     * @param T|ResourceInterface $resource
     *
     * @psalm-param T             $resource
     *
     * @return ResourceEventInterface|\Symfony\Contracts\EventDispatcher\Event
     */
    public function createResourceEvent(ResourceInterface $resource): ResourceEventInterface;

    /**
     * Returns the resource class name.
     *
     * @return string
     */
    public function getClassName(): string;
}

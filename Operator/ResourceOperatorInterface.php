<?php

namespace Ekyna\Component\Resource\Operator;

use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ResourceOperatorInterface
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceOperatorInterface
{
    /**
     * Persists the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @return ResourceEventInterface
     */
    public function persist($resourceOrEvent);

    /**
     * Detaches the resource from the manager.
     *
     * @param ResourceInterface $resource
     */
    public function detach(ResourceInterface $resource);

    /**
     * Merges the resource in the manager.
     *
     * @param ResourceInterface $resource
     */
    public function merge(ResourceInterface $resource);

    /**
     * Refreshes the resource in the manager.
     *
     * @param ResourceInterface $resource
     */
    public function refresh(ResourceInterface $resource);

    /**
     * Clears the manager.
     */
    public function clear();

    /**
     * Creates the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @return ResourceEventInterface
     */
    public function create($resourceOrEvent);

    /**
     * Updates the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     *
     * @return ResourceEventInterface
     */
    public function update($resourceOrEvent);

    /**
     * Deletes the resource.
     *
     * @param ResourceInterface|ResourceEventInterface $resourceOrEvent
     * @param boolean $hard Whether or not to bypass deletion prevention.
     *
     * @return ResourceEventInterface
     */
    public function delete($resourceOrEvent, $hard = false);

    /**
     * Creates the resource event.
     *
     * @param ResourceInterface $resource
     *
     * @return ResourceEventInterface
     */
    public function createResourceEvent($resource);
}

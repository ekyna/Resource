<?php

namespace Ekyna\Component\Resource\Operator;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;

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
     * @param object|ResourceEvent $resourceOrEvent
     *
     * @return ResourceEvent
     */
    public function persist($resourceOrEvent);

    /**
     * Detaches the resource from the manager.
     *
     * @param $resource
     */
    public function detach($resource);

    /**
     * Merges the resource in the manager.
     *
     * @param $resource
     */
    public function merge($resource);

    /**
     * Refreshes the resource in the manager.
     *
     * @param $resource
     */
    public function refresh($resource);

    /**
     * Clears the manager.
     */
    public function clear();

    /**
     * Creates the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     *
     * @return ResourceEvent
     */
    public function create($resourceOrEvent);

    /**
     * Updates the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     *
     * @return ResourceEvent
     */
    public function update($resourceOrEvent);

    /**
     * Deletes the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     * @param boolean $hard Whether to bypass soft deleteable behavior or not.
     *
     * @return ResourceEvent
     */
    public function delete($resourceOrEvent, $hard = false);
}

<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Interface ResourceEventInterface
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventInterface
{
    /**
     * Returns whether further event listeners should be triggered.
     *
     * @see Event::stopPropagation()
     *
     * @return bool Whether propagation was already stopped for this event.
     */
    public function isPropagationStopped();

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation();

    /**
     * Returns the resource.
     *
     * @return ResourceInterface
     */
    public function getResource();

    /**
     * Sets the resource.
     *
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource);

    /**
     * Sets whether the operation must be performed "hardly" or not (for deletion).
     *
     * @param boolean $hard
     * @return ResourceEventInterface|$this
     */
    public function setHard($hard);

    /**
     * Returns whether the operation must be performed "hardly" or not.
     *
     * @return boolean
     */
    public function getHard();

    /**
     * Adds the data.
     *
     * @param string $key
     * @param mixed $value
     * @return ResourceEventInterface|$this
     */
    public function addData($key, $value);

    /**
     * Returns whether there is a data for the given key or not.
     *
     * @param $key
     * @return bool
     */
    public function hasData($key);

    /**
     * Returns the data by key.
     *
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getData($key);

    /**
     * Adds the messages.
     *
     * @param array $messages
     * @return ResourceEventInterface|$this
     */
    public function addMessages(array $messages);

    /**
     * Adds the message.
     *
     * @param ResourceMessage $message
     * @return ResourceEventInterface|$this
     */
    public function addMessage(ResourceMessage $message);

    /**
     * Returns the messages, optionally filtered by type.
     *
     * @param string $type
     * @return array|ResourceMessage[]
     */
    public function getMessages($type = null);

    /**
     * Returns whether the event has messages or not, optionally filtered by type.
     *
     * @param string $type
     * @return bool
     */
    public function hasMessages($type = null);

    /**
     * Returns whether the event has errors or not.
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Returns the error messages.
     *
     * @return array|ResourceMessage[]
     */
    public function getErrors();

    /**
     * Converts messages to flashes.
     *
     * @param FlashBagInterface $flashBag
     * @todo REMOVE the flashbag dependency
     */
    public function toFlashes(FlashBagInterface $flashBag);
}

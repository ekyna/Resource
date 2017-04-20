<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface ResourceEventInterface
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventInterface extends StoppableEventInterface
{
    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation(): void;

    public function getResource(): ?ResourceInterface;

    public function setResource(ResourceInterface $resource): void;

    /**
     * Sets whether the operation must be performed "hardly" or not (for deletion).
     */
    public function setHard(bool $hard): ResourceEventInterface;

    /**
     * Returns whether the operation must be performed "hardly" or not.
     */
    public function getHard(): bool;

    /**
     * Adds the data.
     *
     * @param mixed $value
     *
     * @return $this|ResourceEventInterface
     */
    public function addData(string $key, $value): ResourceEventInterface;

    /**
     * Returns whether there is a data for the given key or not.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData(string $key): bool;

    /**
     * Returns the data by key.
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getData(string $key);

    /**
     * Adds the messages.
     */
    public function addMessages(array $messages): ResourceEventInterface;

    /**
     * Adds the message.
     */
    public function addMessage(ResourceMessage $message): ResourceEventInterface;

    /**
     * Returns the messages, optionally filtered by type.
     *
     * @return array<ResourceMessage>
     */
    public function getMessages(string $type = null): array;

    /**
     * Returns whether the event has messages or not, optionally filtered by type.
     */
    public function hasMessages(string $type = null): bool;

    /**
     * Returns whether the event has errors or not.
     */
    public function hasErrors(): bool;

    /**
     * Returns the error messages.
     *
     * @return array<ResourceMessage>
     */
    public function getErrors(): array;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

use function array_key_exists;
use function count;

/**
 * Class ResourceEvent
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvent extends Event implements ResourceEventInterface
{
    protected ?ResourceInterface $resource = null;
    protected bool               $hard     = false;
    protected array              $data     = [];
    /** @var array<int, ResourceMessage> */
    protected array $messages = [];

    public function getResource(): ?ResourceInterface
    {
        return $this->resource;
    }

    public function setResource(ResourceInterface $resource): void
    {
        $this->resource = $resource;
    }

    public function setHard(bool $hard): ResourceEventInterface
    {
        $this->hard = $hard;

        return $this;
    }

    public function getHard(): bool
    {
        return $this->hard;
    }

    public function addData(string $key, mixed $value): ResourceEventInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function hasData(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function getData(string $key): mixed
    {
        if ($this->hasData($key)) {
            return $this->data[$key];
        }

        throw new InvalidArgumentException("Undefined '$key' data.");
    }

    public function addMessages(array $messages): ResourceEventInterface
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    public function addMessage(ResourceMessage $message): ResourceEventInterface
    {
        if ($message->getType() === ResourceMessage::TYPE_ERROR) {
            $this->stopPropagation();
        }

        $this->messages[] = $message;

        return $this;
    }

    public function getMessages(string $type = null): array
    {
        if (null === $type) {
            return $this->messages;
        }

        ResourceMessage::validateType($type);

        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->getType() === $type) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function hasMessages(string $type = null): bool
    {
        return 0 < count($this->getMessages($type));
    }

    public function hasErrors(): bool
    {
        return $this->hasMessages(ResourceMessage::TYPE_ERROR);
    }

    public function getErrors(): array
    {
        return $this->getMessages(ResourceMessage::TYPE_ERROR);
    }
}

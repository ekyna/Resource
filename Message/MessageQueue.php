<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Message;

use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Messenger\MessageBusInterface;

use function is_callable;
use function is_object;

/**
 * Class MessageQueue
 * @package Ekyna\Bundle\ResourceBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class MessageQueue implements MessageQueueInterface
{
    private ?MessageBusInterface $bus;

    /** @var array<object|callable> */
    private array $queue = [];

    public function __construct(?MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @inheritDoc
     */
    public function addMessage($message): MessageQueueInterface
    {
        $this->queue[] = $message;

        return $this;
    }

    /**
     * Calls messages builders and dispatches messages through the bus.
     */
    public function flush(): void
    {
        if (null === $this->bus) {
            $this->queue = [];

            return;
        }

        foreach ($this->queue as $message) {
            if (is_callable($message)) {
                $message = $message();
            }

            if (!is_object($message)) {
                throw new UnexpectedTypeException($message, 'object');
            }

            $this->bus->dispatch($message);
        }

        $this->queue = [];
    }
}

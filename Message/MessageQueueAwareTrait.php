<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Message;

/**
 * Trait MessageQueueAwareTrait
 * @package Ekyna\Component\Resource\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait MessageQueueAwareTrait
{
    protected readonly MessageQueueInterface $messageQueue;

    public function setMessageQueue(MessageQueueInterface $messageQueue): void
    {
        $this->messageQueue = $messageQueue;
    }
}

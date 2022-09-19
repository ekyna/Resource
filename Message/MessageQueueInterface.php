<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Message;

/**
 * Interface MessageQueueInterface
 * @package Ekyna\Component\Resource\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface MessageQueueInterface
{
    public function addMessage(object|callable $message): MessageQueueInterface;
}
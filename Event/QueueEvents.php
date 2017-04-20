<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

/**
 * Class QueueEvents
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QueueEvents
{
    public const QUEUE_OPEN  = 'ekyna_resource.event_queue_open';
    public const QUEUE_CLOSE = 'ekyna_resource.event_queue_close';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Component\Resource\Event;

/**
 * Class QueueEvents
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QueueEvents
{
    const QUEUE_OPEN  = 'ekyna_resource.event_queue_open';
    const QUEUE_CLOSE = 'ekyna_resource.event_queue_close';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

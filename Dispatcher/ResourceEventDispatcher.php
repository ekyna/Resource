<?php

namespace Ekyna\Component\Resource\Dispatcher;

use Ekyna\Component\Resource\Dispatcher as RD;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class ResourceEventDispatcher
 * @package Ekyna\Component\Resource\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEventDispatcher extends EventDispatcher implements RD\ResourceEventDispatcherInterface
{
    use RD\ResourceEventDispatcherTrait;
}

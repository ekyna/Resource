<?php

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;

/**
 * Class ResourceEvents
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvents
{
    // Persistence
    const INSERT      = 'insert';
    const UPDATE      = 'update';
    const DELETE      = 'delete';

    // Domain
    const INITIALIZE  = 'initialize';

    const PRE_CREATE  = 'pre_create';
    const POST_CREATE = 'post_create';

    const PRE_UPDATE  = 'pre_update';
    const POST_UPDATE = 'post_update';

    const PRE_DELETE  = 'pre_delete';
    const POST_DELETE = 'post_delete';


    /**
     * Returns the event names.
     *
     * @return array
     */
    static public function getNames()
    {
        return [
            static::INSERT,
            static::UPDATE,
            static::DELETE,

            static::INITIALIZE,

            static::PRE_CREATE,
            static::POST_CREATE,

            static::PRE_UPDATE,
            static::POST_UPDATE,

            static::PRE_DELETE,
            static::POST_DELETE,
        ];
    }

    /**
     * Checks whether or not the given event name is valid.
     *
     * @param string $name
     * @param bool $throwException
     *
     * @return bool
     */
    static public function isValid($name, $throwException = true)
    {
        if (in_array($name, static::getNames(), true)) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException("Unexpected event name '$name'.");
        }

        return false;
    }
}

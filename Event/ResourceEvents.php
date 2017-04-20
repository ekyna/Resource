<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;

use function in_array;

/**
 * Class ResourceEvents
 * @package Ekyna\Component\Resource\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ResourceEvents
{
    // Persistence
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    // Domain
    public const INITIALIZE = 'initialize';

    public const PRE_CREATE  = 'pre_create';
    public const POST_CREATE = 'post_create';

    public const PRE_UPDATE  = 'pre_update';
    public const POST_UPDATE = 'post_update';

    public const PRE_DELETE  = 'pre_delete';
    public const POST_DELETE = 'post_delete';


    /**
     * Returns the event names.
     *
     * @return array
     */
    public static function getNames(): array
    {
        return [
            self::INSERT,
            self::UPDATE,
            self::DELETE,

            self::PRE_CREATE,
            self::POST_CREATE,

            self::PRE_UPDATE,
            self::POST_UPDATE,

            self::PRE_DELETE,
            self::POST_DELETE,
        ];
    }

    /**
     * Checks whether or not the given event name is valid.
     *
     * @param string $name
     * @param bool   $throwException
     *
     * @return bool
     */
    public static function isValid(string $name, bool $throwException = true): bool
    {
        if (in_array($name, self::getNames(), true)) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException("Unexpected event name '$name'.");
        }

        return false;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

use Ekyna\Component\Resource\Exception\UnexpectedValueException;

/**
 * Class Behaviors
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Behaviors
{
    public const INSERT   = 'onInsert';
    public const UPDATE   = 'onUpdate';
    public const DELETE   = 'onDelete';
    public const LOAD     = 'onLoad';
    public const METADATA = 'onMetadata';

    private const METHOD_MAP = [
        self::INSERT   => 'onInsert',
        self::UPDATE   => 'onUpdate',
        self::DELETE   => 'onDelete',
        self::LOAD     => 'onLoad',
        self::METADATA => 'onMetadata',
    ];


    /**
     * Returns whether the given behavior operation is valid.
     *
     * @param string $operation The operation
     * @param bool   $throw     Whether to throw an exception if not valid.
     *
     * @return bool
     */
    public static function isValid(string $operation, bool $throw = true): bool
    {
        if (isset(self::METHOD_MAP[$operation])) {
            return true;
        }

        if ($throw) {
            throw new UnexpectedValueException("Invalid behavior operation '$operation'.'");
        }

        return false;
    }

    /**
     * Returns the behavior method name for the given operation.
     *
     * @param string $operation
     *
     * @return string
     */
    public static function getMethod(string $operation): string
    {
        self::isValid($operation);

        return self::METHOD_MAP[$operation];
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

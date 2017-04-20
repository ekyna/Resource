<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use Throwable;

use function array_slice;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_object;
use function sprintf;

/**
 * Class UnexpectedTypeException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedTypeException extends UnexpectedValueException
{
    /**
     * Constructor.
     *
     * @param mixed           $value
     * @param string|string[] $types
     * @param int             $code
     * @param Throwable|null  $previous
     */
    public function __construct($value, $types, $code = 0, Throwable $previous = null)
    {
        if (empty($types)) {
            parent::__construct('Unexpected type', $code, $previous);

            return;
        }

        $types = (array)$types;

        if (1 === $length = count($types)) {
            $types = $types[0];
        } else {
            $types = implode(', ', array_slice($types, 0, $length - 2)) . ' or ' . $types[$length - 1];
        }

        $value = is_object($value) ? get_class($value) : gettype($value);

        parent::__construct(sprintf('Expected %s, got %s', $types, $value), $code, $previous);
    }
}

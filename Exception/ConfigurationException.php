<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use Exception;
use Throwable;

use function sprintf;

/**
 * Class ConfigurationException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationException extends Exception implements ResourceExceptionInterface
{
    public static function create(string $resource, string $actionOrBehavior, Throwable $cause = null): self
    {
        $message = sprintf(
            '%s has invalid option(s) for resource %s%s',
            $actionOrBehavior, $resource, $cause ? ":\n" . $cause->getMessage() : ''
        );

        throw new self($message, 0, $cause);
    }
}

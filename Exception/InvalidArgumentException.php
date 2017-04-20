<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use InvalidArgumentException as BaseException;

/**
 * Class InvalidArgumentException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvalidArgumentException extends BaseException implements ResourceExceptionInterface
{

}

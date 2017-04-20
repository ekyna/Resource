<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use UnexpectedValueException as BaseException;

/**
 * Class UnexpectedValueException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedValueException extends BaseException implements ResourceExceptionInterface
{

}

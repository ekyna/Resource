<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use LogicException as BaseException;

/**
 * Class LogicException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LogicException extends BaseException implements ResourceExceptionInterface
{

}

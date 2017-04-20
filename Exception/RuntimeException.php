<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Exception;

use RuntimeException as BaseException;

/**
 * Class RuntimeException
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RuntimeException extends BaseException implements ResourceExceptionInterface
{

}

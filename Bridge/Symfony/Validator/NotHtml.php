<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class NotHtml
 * @package Ekyna\Component\Resource\Bridge\Symfony\Validator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotHtml extends Constraint
{
    public string $message = 'This value contains unauthorized HTML code';
}

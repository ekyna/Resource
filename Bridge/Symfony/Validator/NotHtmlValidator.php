<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function is_string;
use function strip_tags;

/**
 * Class NotHtmlValidator
 * @package Ekyna\Component\Resource\Bridge\Symfony\Validator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotHtmlValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        if (!$constraint instanceof NotHtml) {
            throw new UnexpectedTypeException($constraint, NotHtml::class);
        }

        if ($value === strip_tags($value)) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->message)
            ->addViolation();
    }
}

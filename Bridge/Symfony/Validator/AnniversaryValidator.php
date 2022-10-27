<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Validator;

use DateTime;
use Ekyna\Component\Resource\Model\Anniversary as Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Throwable;

use function date_create_from_format;
use function is_null;
use function sprintf;

/**
 * Class AnniversaryValidator
 * @package Ekyna\Component\Resource\Bridge\Symfony\Validator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AnniversaryValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (is_null($value)) {
            return;
        }

        if (!$value instanceof Model) {
            throw new UnexpectedTypeException($value, Model::class);
        }
        if (!$constraint instanceof Anniversary) {
            throw new UnexpectedTypeException($constraint, Anniversary::class);
        }

        $month = $value->getMonth();
        if (!(0 < $month && $month <= 12)) {
            $this
                ->context
                ->buildViolation('Please select a month.')
                ->atPath('month')
                ->addViolation();

            return;
        }

        $day = $value->getDay();
        if (!(0 < $day && $day <= 31)) {
            $this
                ->context
                ->buildViolation('Please select a day.')
                ->atPath('day')
                ->addViolation();

            return;
        }

        try {
            $date = DateTime::createFromFormat('Y-m-d', sprintf('2020-%s-%s', $month, $day));
        } catch (Throwable) {
            $date = false;
        }

        if ($date instanceof DateTime) {
            return;
        }

        $this
            ->context
            ->buildViolation('Invalid date.')
            ->atPath('day')
            ->addViolation();
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidationHelper
 * @package Ekyna\Component\Resource\Bridge\Symfony\Validator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ValidationHelper
{
    private ?PropertyAccessor $propertyAccessor = null;

    public function __construct(
        private readonly ExecutionContextInterface $context
    ) {
    }

    public function validate(object $object, array $config): void
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        $validator = $this->context->getValidator();

        foreach ($config as $field => $constraints) {
            $value = $this->propertyAccessor->getValue($object, $field);

            $violationList = $validator->validate($value, $constraints);

            if (0 === $violationList->count()) {
                continue;
            }

            /** @var ConstraintViolationInterface $violation */
            foreach ($violationList as $violation) {
                $this->context
                    ->buildViolation($violation->getMessage())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->atPath($field)
                    ->addViolation();
            }

            break;
        }
    }
}

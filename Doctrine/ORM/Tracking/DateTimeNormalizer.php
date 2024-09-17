<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Tracking;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function is_null;

/**
 * Class DateTimeNormalizer
 * @package Ekyna\Component\Resource\Doctrine\ORM\Tracking
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DateTimeNormalizer implements NormalizerInterface
{
    public function getTypes(): array
    {
        return [
            Types::DATE_MUTABLE,
            Types::DATE_IMMUTABLE,
            Types::DATETIME_MUTABLE,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIMETZ_MUTABLE,
            Types::DATETIMETZ_IMMUTABLE,
            Types::TIME_MUTABLE,
            Types::TIME_IMMUTABLE,
        ];
    }

    /**
     * @inheritDoc
     */
    public function convert(mixed $value, array $mapping): null|string|array
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.u P');
        }

        throw new UnexpectedTypeException($value, [DateTimeInterface::class, 'null']);
    }
}

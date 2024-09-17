<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Tracking;

use Decimal\Decimal;
use Ekyna\Component\Resource\Doctrine\DBAL\Type\PhpDecimalType;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function is_null;

/**
 * Class DecimalNormalizer
 * @package Ekyna\Component\Resource\Doctrine\ORM\Tracking
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DecimalNormalizer implements NormalizerInterface
{
    public function getTypes(): array
    {
        return [PhpDecimalType::NAME];
    }

    /**
     * @inheritDoc
     */
    public function convert(mixed $value, array $mapping): null|string|array
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Decimal) {
            return $value->toFixed($mapping['scale']);
        }

        throw new UnexpectedTypeException($value, [Decimal::class, 'null']);
    }
}

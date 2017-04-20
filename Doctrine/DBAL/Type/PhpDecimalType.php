<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\DBAL\Type;

use Decimal\Decimal;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DecimalType;

use function is_null;
use function strlen;

/**
 * Class PhpDecimalType
 * @package Ekyna\Component\Resource\Doctrine\DBAL\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PhpDecimalType extends DecimalType
{
    public const NAME = 'php_decimal';

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value) || 0 === strlen($value)) {
            return null;
        }

        return new Decimal((string)$value);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Decimal) {
            return $value->toString();
        }

        throw new ConversionException('Expected instance of ' . Decimal::class);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return static::NAME;
    }
}

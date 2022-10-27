<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Resource\Model\Anniversary;

use function explode;
use function is_null;
use function strlen;

/**
 * Class PhpDecimalType
 * @package Ekyna\Component\Resource\Doctrine\DBAL\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AnniversaryType extends Type
{
    public const NAME = 'anniversary';

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value) || 0 === strlen($value)) {
            return null;
        }

        [$month, $day] = explode('-', $value);

        return new Anniversary((int)$month, (int)$day);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Anniversary) {
            return $value->toString();
        }

        throw new ConversionException('Expected instance of ' . Anniversary::class);
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL(['length' => 5]);
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

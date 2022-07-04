<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Class EncryptedJsonArrayType
 * @package Ekyna\Bundle\CoreBundle\Doctrine\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EncryptedJsonType extends JsonType
{
    use EncryptorTrait;

    public const NAME = 'encrypted_json';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (empty($value)) {
            return null;
        }

        $encoded = parent::convertToDatabaseValue($value, $platform);

        return $this->getEncryptor($platform)->encrypt($encoded);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (empty($value)) {
            return null;
        }

        $decrypted = $this->getEncryptor($platform)->decrypt($value);

        return parent::convertToPHPValue($decrypted, $platform);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}

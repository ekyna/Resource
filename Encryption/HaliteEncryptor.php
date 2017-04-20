<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Encryption;

use ParagonIE\Halite\Alerts;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;

/**
 * Class HaliteEncryptor
 * @package Ekyna\Bundle\CoreBundle\Service\Encryptor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HaliteEncryptor implements EncryptorInterface
{
    private ?EncryptionKey $encryptionKey = null;
    private string         $keyFile;


    /**
     * Constructor.
     *
     * @param string $keyFile
     */
    public function __construct(string $keyFile)
    {
        $this->keyFile = $keyFile;
    }

    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        return Crypto::encrypt(new HiddenString($data), $this->getKey());
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $data): string
    {
        return Crypto::decrypt($data, $this->getKey())->getString();
    }

    /**
     * Returns the encryption key.
     *
     * @return EncryptionKey|null
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getKey(): ?EncryptionKey
    {
        if ($this->encryptionKey === null) {
            if (!is_dir($directory = dirname($this->keyFile))) {
                if (!mkdir($directory)) {
                    throw new Alerts\CannotPerformOperation('Cannot create directory: ' . $directory);
                }
            }

            try {
                $this->encryptionKey = KeyFactory::loadEncryptionKey($this->keyFile);
            } catch (Alerts\CannotPerformOperation $e) {
                $this->encryptionKey = KeyFactory::generateEncryptionKey();
                KeyFactory::save($this->encryptionKey, $this->keyFile);
            }
        }

        return $this->encryptionKey;
    }
}
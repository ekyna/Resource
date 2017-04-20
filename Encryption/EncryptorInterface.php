<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Encryption;

/**
 * Interface EncryptorInterface
 * @package Ekyna\Component\Resource
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface EncryptorInterface
{
    /**
     * Encrypts the given data.
     *
     * @param string $data Plain text to encrypt
     *
     * @return string Encrypted text
     */
    public function encrypt(string $data): string;

    /**
     * Decrypts the given data.
     *
     * @param string $data Encrypted text
     *
     * @return string Plain text
     */
    public function decrypt(string $data): string;
}

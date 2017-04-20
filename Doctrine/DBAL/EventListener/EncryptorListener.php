<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\DBAL\EventListener;

use Ekyna\Component\Resource\Encryption\EncryptorInterface;

/**
 * Class EncryptorListener
 * @package Ekyna\Bundle\CoreBundle\Doctrine\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see http://emanueleminotto.github.io/blog/service-injection-doctrine-dbal-type
 */
class EncryptorListener
{
    private EncryptorInterface $encryptor;


    /**
     * Constructor.
     *
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Returns the encryptor.
     *
     * @return EncryptorInterface
     */
    public function getEncryptor(): EncryptorInterface
    {
        return $this->encryptor;
    }
}

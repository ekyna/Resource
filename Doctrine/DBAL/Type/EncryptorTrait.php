<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ekyna\Component\Resource\Doctrine\DBAL\EventListener\EncryptorListener;
use Ekyna\Component\Resource\Encryption\EncryptorInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Trait EncryptorTrait
 * @package Ekyna\Bundle\CoreBundle\Doctrine\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait EncryptorTrait
{
    protected ?EncryptorInterface $encryptor = null;


    /**
     * Returns the encryptor.
     *
     * @param AbstractPlatform $platform
     *
     * @return EncryptorInterface
     */
    protected function getEncryptor(AbstractPlatform $platform): EncryptorInterface
    {
        if ($this->encryptor) {
            return $this->encryptor;
        }

        $listeners = $platform->getEventManager()->getListeners('getEncryptor');
        $listener = array_shift($listeners);

        if (!$listener instanceof EncryptorListener) {
            throw new RuntimeException('Encryptor is not available');
        }

        return $this->encryptor = $listener->getEncryptor();
    }
}

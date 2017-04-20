<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Factory;

use Ekyna\Component\Resource\Doctrine\ORM\Repository\LocaleAwareRepositoryTrait;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Factory\TranslatableFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Class TranslatableFactory
 * @package Ekyna\Component\Resource\Doctrine\ORM\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslatableFactory extends ResourceFactory implements TranslatableFactoryInterface
{
    use LocaleAwareRepositoryTrait;

    public function create(): ResourceInterface
    {
        $resource = parent::create();

        if (!$resource instanceof TranslatableInterface) {
            throw new UnexpectedTypeException($resource, TranslatableInterface::class);
        }

        $resource->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $resource->setFallbackLocale($this->localeProvider->getFallbackLocale());

        return $resource;
    }
}

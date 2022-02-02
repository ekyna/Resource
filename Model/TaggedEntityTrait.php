<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Trait TaggedEntityTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 * @deprecated
 * @TODO    Remove
 */
trait TaggedEntityTrait
{
    /**
     * Returns the entity tag.
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getEntityTag(): string
    {
        if (null === $this->getId()) {
            throw new \RuntimeException('Unable to generate entity tag, as the id property is undefined.');
        }

        return sprintf('%s[id:%s]', static::getEntityTagPrefix(), $this->getId());
    }

    /**
     * Returns the entity and his related entities tags.
     *
     * @return array
     */
    public function getEntityTags(): array
    {
        return [$this->getEntityTag()];
    }

    /**
     * Returns the entity tag.
     *
     * @return string
     */
    public static function getEntityTagPrefix(): string
    {
        // TODO return null by default and get the prefix from the resource configuration.
        throw new \BadMethodCallException('Must be implemented');
    }
}

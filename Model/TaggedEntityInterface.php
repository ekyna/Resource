<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Interface TaggedEntityInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated
 */
interface TaggedEntityInterface extends ResourceInterface
{
    /**
     * Returns the entity tag.
     *
     * @throws \RuntimeException
     * @return string
     * @deprecated
     */
    public function getEntityTag(): string;

    /**
     * Returns the entity tag and his related entities tags.
     *
     * @return array
     * @deprecated
     */
    public function getEntityTags(): array;

    /**
     * Returns the entity tag prefix.
     *
     * @return string
     * @deprecated
     */
    public static function getEntityTagPrefix(): string;
}

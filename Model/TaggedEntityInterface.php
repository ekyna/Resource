<?php

namespace Ekyna\Component\Resource\Model;

/**
 * Interface TaggedEntityInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TaggedEntityInterface extends ResourceInterface
{
    /**
     * Returns the entity tag.
     *
     * @throws \RuntimeException
     * @return string
     */
    public function getEntityTag();

    /**
     * Returns the entity tag and his related entities tags.
     *
     * @return array
     */
    public function getEntityTags();

    /**
     * Returns the entity tag prefix.
     *
     * @return string
     */
    public static function getEntityTagPrefix();
}

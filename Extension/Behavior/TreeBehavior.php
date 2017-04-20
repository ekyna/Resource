<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Extension\Behavior;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Behavior\AbstractBehavior;

/**
 * Class TreeBehavior
 * @package Ekyna\Component\Resource\Extension\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TreeBehavior extends AbstractBehavior
{
    /**
     * @inheritDoc
     */
    public static function configureBehavior(): array
    {
        // TODO: Implement configureBehavior() method.
    }

    /**
     * @inheritDoc
     */
    public function onMetadata(ClassMetadataInfo $metadata, array $options): void
    {
        // TODO
        // repository-class
        // left, right, root, level
        // parent / children
        // gedmo:tree type = nested
    }
}

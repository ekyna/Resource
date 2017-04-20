<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;


use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BehaviorExecutorInterface
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BehaviorExecutorInterface
{
    /**
     * Executes the resource behaviors for the given operation.
     */
    public function execute(ResourceInterface $resource, string $operation): void;

    /**
     * Loads class metadata.
     */
    public function metadata(ClassMetadata $metadata): void;
}

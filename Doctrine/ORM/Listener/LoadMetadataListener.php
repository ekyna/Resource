<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\ORMException;
use Ekyna\Component\Resource\Behavior\BehaviorExecutorInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function class_parents;
use function in_array;

/**
 * Class LoadMetadataListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoadMetadataListener
{
    protected BehaviorExecutorInterface $behaviorExecutor;
    /** @var array<int, string> */
    protected array $classes;

    public function __construct(BehaviorExecutorInterface $behaviorExecutor, array $classes)
    {
        $this->behaviorExecutor = $behaviorExecutor;
        $this->classes          = $classes;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @throws ORMException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();

        if (in_array($metadata->getName(), $this->classes, true)) {
            $metadata->isMappedSuperclass = false;
        }

        if (!$metadata->isMappedSuperclass) {
            $this->setAssociationMappings($metadata, $eventArgs->getEntityManager()->getConfiguration());
        } else {
            $this->unsetAssociationMappings($metadata);
        }

        if ($metadata->getReflectionClass()->implementsInterface(ResourceInterface::class)) {
            $this->behaviorExecutor->metadata($metadata);
        }
    }

    /**
     * Moves associations mappings over entities class inheritance.
     *
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     *
     * @throws ORMException
     */
    private function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration): void
    {
        foreach (class_parents($metadata->getName()) as $parent) {
            $driver = $configuration->getMetadataDriverImpl();

            if (!in_array($parent, $driver->getAllClassNames())) {
                continue;
            }

            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );

            $driver->loadMetadataForClass($parent, $parentMetadata);

            if (!$parentMetadata->isMappedSuperclass) {
                continue;
            }

            foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                if ($this->hasRelation($value['type'])) {
                    $metadata->associationMappings[$key] = $value;
                }
            }
        }
    }

    /**
     * Clears associations mappings over entities class inheritance.
     *
     * @param ClassMetadataInfo $metadata
     */
    private function unsetAssociationMappings(ClassMetadataInfo $metadata): void
    {
        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->hasRelation($value['type'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    /**
     * Returns whether the mapping type is an association.
     *
     * @param int $type
     *
     * @return bool
     */
    private function hasRelation(int $type): bool
    {
        return in_array($type, [
            ClassMetadataInfo::MANY_TO_MANY,
            ClassMetadataInfo::ONE_TO_MANY,
            ClassMetadataInfo::ONE_TO_ONE,
        ], true);
    }
}

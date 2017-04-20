<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Factory;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use function sprintf;

/**
 * Class ResourceFactory
 * @package Ekyna\Component\Resource\Doctrine\ORM\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceFactory implements ResourceFactoryInterface
{
    private ManagerRegistry $managerRegistry;
    private string          $class;

    public function setManagerRegistry(ManagerRegistry $registry): void
    {
        $this->managerRegistry = $registry;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function create(): ResourceInterface
    {
        return new $this->class();
    }

    public function createFromContext(Context $context): ResourceInterface
    {
        $config = $context->getConfig();

        if ($config->getEntityClass() !== $this->class) {
            throw new RuntimeException('Resource factory / context miss match.');
        }

        $resource = $this->create();

        if ($config->getParentId()) {
            if (!$parentContext = $context->getParent()) {
                throw new RuntimeException('Parent context is not available.');
            }

            if (!$parentConfig = $parentContext->getConfig()) {
                throw new RuntimeException('Parent config is not available.');
            }

            if (!$parentResource = $parentContext->getResource()) {
                throw new RuntimeException('Parent resource is not available.');
            }

            /** @var ClassMetadataInfo $metadata */
            $metadata = $this
                ->managerRegistry
                ->getManagerForClass($this->class)
                ->getClassMetadata($this->class);

            $associations = $metadata->getAssociationsByTargetClass($parentConfig->getEntityClass());

            if (!empty($associations)) {
                foreach ($associations as $mapping) {
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                        try {
                            PropertyAccess::createPropertyAccessor()
                                ->setValue($resource, $mapping['fieldName'], $parentResource);
                        } catch (InvalidArgumentException $e) {
                            throw new RuntimeException('Failed to set resource\'s parent.');
                        }

                        return $resource;
                    }
                }
            }

            throw new RuntimeException(sprintf(
                "Association '%s' not found or not supported.",
                $config->getCamelCaseName()
            ));
        }

        return $resource;
    }
}

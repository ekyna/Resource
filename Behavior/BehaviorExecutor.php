<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Config\Registry\BehaviorRegistryInterface as BehaviorConfigRegistry;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface as ResourceRegistry;
use Ekyna\Component\Resource\Model\ResourceInterface;

use Ekyna\Component\Resource\Model\TranslationInterface;

use function array_replace_recursive;
use function call_user_func_array;
use function in_array;
use function is_subclass_of;

/**
 * Class BehaviorExecutor
 * @package Ekyna\Component\Resource\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class BehaviorExecutor implements BehaviorExecutorInterface
{
    protected ResourceRegistry $resourceRegistry;
    protected BehaviorConfigRegistry $configRegistry;
    protected BehaviorRegistryInterface $serviceRegistry;

    public function __construct(
        ResourceRegistry $resourceRegistry,
        BehaviorConfigRegistry $configRegistry,
        BehaviorRegistryInterface $serviceRegistry
    ) {
        $this->resourceRegistry = $resourceRegistry;
        $this->configRegistry = $configRegistry;
        $this->serviceRegistry = $serviceRegistry;
    }

    public function execute(ResourceInterface $resource, string $operation): void
    {
        if ($resource instanceof TranslationInterface) {
            $resourceConfig = $this->resourceRegistry->findByTranslation($resource, false);
        } else {
            $resourceConfig = $this->resourceRegistry->find($resource, false);
        }

        if (!$resourceConfig) {
            return;
        }

        if (empty($behaviors = $resourceConfig->getBehaviors())) {
            return;
        }

        foreach ($behaviors as $behaviorName => $options) {
            $behaviorConfig = $this->configRegistry->find($behaviorName);

            if (!in_array($operation, $behaviorConfig->getOperations(), true)) {
                continue;
            }

            $behavior = $this->serviceRegistry->getBehavior($behaviorName);

            call_user_func_array(
                [$behavior, Behaviors::getMethod($operation)],
                [$resource, array_replace_recursive($behaviorConfig->getDefaultOptions(), $options)]
            );
        }
    }

    public function metadata(ClassMetadata $metadata): void
    {
        $class = $metadata->getName();

        if (is_subclass_of($class, TranslationInterface::class)) {
            $resourceConfig = $this->resourceRegistry->findByTranslation($class, false);
        } else {
            $resourceConfig = $this->resourceRegistry->find($class, false);
        }

        if (null === $resourceConfig) {
            return;
        }

        if (empty($behaviors = $resourceConfig->getBehaviors())) {
            return;
        }

        foreach ($behaviors as $behaviorName => $options) {
            $behaviorConfig = $this->configRegistry->find($behaviorName);

            if (!in_array(Behaviors::METADATA, $behaviorConfig->getOperations(), true)) {
                continue;
            }

            $behavior = $this->serviceRegistry->getBehavior($behaviorName);

            $behavior->onMetadata($metadata, array_replace_recursive($behaviorConfig->getDefaultOptions(), $options));
        }
    }
}

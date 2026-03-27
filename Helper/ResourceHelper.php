<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ResourceHelper
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class ResourceHelper implements ResourceHelperInterface
{
    public function __construct(
        protected readonly ActionRegistryInterface          $actionRegistry,
        protected readonly ResourceRegistryInterface        $resourceRegistry,
        protected readonly ManagerFactoryInterface          $managerFactory,
    ) {
    }

    public function getActionConfig(string $action): ActionConfig
    {
        return $this->actionRegistry->find($action);
    }

    public function getResourceConfig(ResourceInterface|string $resource): ResourceConfig
    {
        return $this->resourceRegistry->find($resource);
    }

    public function hasAction(ResourceInterface|string $resource, string $action): bool
    {
        $aCfg = $this->actionRegistry->find($action);

        return $this
            ->resourceRegistry
            ->find($resource)
            ->hasAction($aCfg->getClass());
    }

    public function getManager(string $class): ResourceManagerInterface
    {
        return $this->managerFactory->getManager($class);
    }
}

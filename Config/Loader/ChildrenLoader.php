<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Loader;

use Closure;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\RuntimeException;

use Generator;

use function sprintf;

/**
 * Class ChildrenLoader
 * @package Ekyna\Component\Resource\Config\Loader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class ChildrenLoader
{
    public static function create(ResourceRegistryInterface $registry): Closure
    {
        return (function () use ($registry): array {
            if (!$this instanceof ResourceConfig) {
                throw new LogicException('ChildrenLoader must be bound to ResourceConfig object.');
            }

            $children = [];
            foreach ($registry->all() as $child) {
                if ($child->getParentId() !== $this->getId()) {
                    continue;
                }

                if ($child->getNamespace() !== $this->getNamespace()) {
                    throw new RuntimeException(sprintf(
                        "Expected '%s' namespace, for '%'.",
                        $this->getNamespace(),
                        $child->getId()
                    ));
                }

                $children[$child->getId()] = $child;
            }

            return $children;
        })(...);
    }

    private function __construct()
    {
    }
}

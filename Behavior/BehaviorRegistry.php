<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Behavior;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class BehaviorRegistry
 * @package Ekyna\Component\Resource\Behavior
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BehaviorRegistry implements BehaviorRegistryInterface
{
    private ContainerInterface $locator;
    private array $aliases;

    /**
     * Constructor.
     *
     * @param ContainerInterface $locator
     * @param array $aliases
     */
    public function __construct(ContainerInterface $locator, array $aliases)
    {
        $this->locator = $locator;
        $this->aliases = $aliases;
    }

    /**
     * @inheritDoc
     */
    public function hasBehavior(string $name): bool
    {
        return $this->locator->has(
            $this->alias($name)
        );
    }

    /**
     * @inheritDoc
     */
    public function getBehavior(string $name): BehaviorInterface
    {
        if (!$this->hasBehavior($name)) {
            throw new InvalidArgumentException("No resource behavior registered under the name '$name'.");
        }

        return $this->locator->get($this->alias($name));
    }

    /**
     * Replaces the name using the aliases map.
     *
     * @param string $name
     *
     * @return string
     */
    private function alias(string $name): string
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }
}

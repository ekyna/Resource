<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Resource\Config\Factory\RegistryFactory as Wrapped;
use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Registry;
use Ekyna\Component\Resource\Exception\LogicException;

/**
 * Class RegistryFactory
 * @package Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class RegistryFactory implements RegistryFactoryInterface
{
    private Wrapped $wrapped;
    private bool    $ready = false;


    public function __construct(Wrapped $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function setReady(): void
    {
        $this->ready = true;
    }

    /**
     * Returns the permission registry.
     */
    public function getPermissionRegistry(): Registry\PermissionRegistryInterface
    {
        $this->assertReady();

        return $this->wrapped->getPermissionRegistry();
    }

    /**
     * Returns the action registry.
     */
    public function getActionRegistry(): Registry\ActionRegistryInterface
    {
        $this->assertReady();

        return $this->wrapped->getActionRegistry();
    }

    /**
     * Returns the behavior registry.
     */
    public function getBehaviorRegistry(): Registry\BehaviorRegistryInterface
    {
        $this->assertReady();

        return $this->wrapped->getBehaviorRegistry();
    }

    /**
     * Returns the namespace registry.
     */
    public function getNamespaceRegistry(): Registry\NamespaceRegistryInterface
    {
        $this->assertReady();

        return $this->wrapped->getNamespaceRegistry();
    }

    /**
     * Returns the resource registry.
     */
    public function getResourceRegistry(): Registry\ResourceRegistryInterface
    {
        $this->assertReady();

        return $this->wrapped->getResourceRegistry();
    }

    private function assertReady(): void
    {
        if ($this->ready) {
            return;
        }

        throw new LogicException('Registry factory is not ready.');
    }
}

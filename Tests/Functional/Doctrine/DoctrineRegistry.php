<?php

namespace Ekyna\Component\Resource\Tests\Functional\Doctrine;

use Doctrine\Persistence\ManagerRegistry as BaseRegistry;
use Doctrine\ORM\ORMException;

/**
 * Class DoctrineRegistry
 * @package Ekyna\Component\Resource\Tests\Functional
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineRegistry implements BaseRegistry
{
    /**
     * @var \Doctrine\DBAL\Driver\Connection[]
     */
    private $connections;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager[]
     */
    private $managers;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $defaultManager;

    /**
     * @var string
     */
    private $proxyInterfaceName;


    /**
     * Constructor.
     *
     * @param array  $connections
     * @param array  $managers
     * @param string $defaultConnection
     * @param string $defaultManager
     * @param string $proxyInterfaceName
     */
    public function __construct(array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName)
    {
        $this->connections = $connections;
        $this->managers = $managers;
        $this->defaultConnection = $defaultConnection;
        $this->defaultManager = $defaultManager;
        $this->proxyInterfaceName = $proxyInterfaceName;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnection;
    }

    /**
     * @inheritDoc
     */
    public function getConnection($name = null)
    {
        if (null === $name) {
            $name = $this->defaultConnection;
        }

        if (!isset($this->connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine connection named "%s" does not exist.', $name));
        }

        return $this->connections[$name];
    }

    /**
     * @inheritDoc
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * @inheritDoc
     */
    public function getConnectionNames()
    {
        return array_keys($this->connections);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultManagerName()
    {
        return $this->defaultManager;
    }

    /**
     * @inheritDoc
     */
    public function getManager($name = null)
    {
        if (null === $name) {
            $name = $this->defaultManager;
        }

        if (!isset($this->managers[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine manager named "%s" does not exist.', $name));
        }

        return $this->managers[$name];
    }

    /**
     * @inheritDoc
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * @inheritDoc
     */
    public function resetManager($name = null)
    {
        // TODO ?
        return $this->getManager($name);
    }

    /**
     * @inheritDoc
     */
    public function getAliasNamespace($alias)
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw ORMException::unknownEntityNamespace($alias);
    }

    /**
     * @inheritDoc
     */
    public function getManagerNames()
    {
        return array_keys($this->managers);
    }

    /**
     * @inheritDoc
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * @inheritDoc
     */
    public function getManagerForClass($class)
    {
        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class, 2);
            $class = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new \ReflectionClass($class);

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            if (! $parentClass = $proxyClass->getParentClass()) {
                return null;
            }

            $class = $parentClass->getName();
        }

        foreach ($this->managers as $manager) {
            if (!$manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }

        throw ORMException::unknownEntityNamespace($class);
    }
}

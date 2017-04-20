<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Persistence\ManagerRegistry as DoctrineRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

/**
 * Class ManagerRegistry
 * @package Ekyna\Component\Resource\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ManagerRegistry
{
    private DoctrineRegistry $wrapped;

    private ?EntityManagerInterface $currentManager = null;


    /**
     * Constructor.
     *
     * @param DoctrineRegistry $wrapped
     */
    public function __construct(DoctrineRegistry $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * Gets a named object manager.
     *
     * @param string|null $name The object manager name (null for the default one).
     *
     * @return ObjectManager
     *
     * @see \Doctrine\Persistence\AbstractManagerRegistry::getManager
     */
    public function getManager(string $name = null): ObjectManager
    {
        return $this->wrapped->getManager($name);
    }

    /**
     *
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     *
     * @return ObjectManager|null
     *
     * @see \Doctrine\Persistence\AbstractManagerRegistry::getManagerForClass
     */
    public function getManagerForClass(string $class): ?ObjectManager
    {
        if ($this->doCurrentManagerSupports($class)) {
            return $this->currentManager;
        }

        return $this->wrapped->getManagerForClass($class);
    }

    /**
     * Gets the ObjectRepository for a persistent object.
     *
     * @param string      $class The name of the persistent object.
     * @param string|null $name  The object manager name (null for the default one).
     *
     * @return ObjectRepository
     */
    public function getRepository(string $class, string $name = null): ?ObjectRepository
    {
        if ($this->doCurrentManagerSupports($class)) { // TODO Check name ?
            $this->currentManager->getRepository($class);
        }

        return $this->wrapped->getRepository($class, $name);
    }

    /**
     * Returns whether the current manager (flush event) supports the given entity class.
     *
     * @param string $class
     *
     * @return bool
     */
    private function doCurrentManagerSupports(string $class): bool
    {
        if (null === $this->currentManager) {
            return false;
        }

        if ($this->currentManager->getMetadataFactory()->hasMetadataFor($class)) {
            return true;
        }

        return false;
    }

    /**
     * Pre flush event listener.
     *
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event): void
    {
        $this->currentManager = $event->getEntityManager();
    }

    /**
     * Post flush event listener.
     */
    public function postFlush(): void
    {
        $this->currentManager = null;
    }
}

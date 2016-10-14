<?php

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class PersistenceHelper
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PersistenceHelper implements PersistenceHelperInterface
{
    /**
     * // TODO use doctrine registry to get resource own manager (if not default)
     * @var EntityManagerInterface
     */
    protected $manager;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns the manager.
     *
     * @return EntityManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @inheritdoc
     */
    public function getChangeSet(ResourceInterface $resource)
    {
        return $this->manager
            ->getUnitOfWork()
            ->getEntityChangeSet($resource);
    }

    /**
     * @inheritdoc
     */
    public function isChanged(ResourceInterface $resource, $properties)
    {
        $changeSet = $this->getChangeSet($resource);

        if (is_string($properties)) {
            return array_key_exists($properties, $changeSet);
        } elseif (is_array($properties)) {
            return 0 < count(array_intersect($properties, array_keys($changeSet)));
        }

        throw new \InvalidArgumentException('Expected string or array.');
    }

    /**
     * @inheritdoc
     */
    public function persistAndRecompute(ResourceInterface $resource)
    {
        $uow = $this->manager->getUnitOfWork();

        if (!($uow->isScheduledForInsert($resource) || $uow->isScheduledForUpdate($resource))) {
            $uow->persist($resource);
        }

        $metadata = $this->manager->getClassMetadata(get_class($resource));
        if ($uow->getEntityChangeSet($resource)) {
            $uow->recomputeSingleEntityChangeSet($metadata, $resource);
        } else {
            $uow->computeChangeSet($metadata, $resource);
        }
    }
}

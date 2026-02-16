<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Elastica;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Doctrine\Listener;

use function array_keys;
use function Symfony\Component\String\u;

/**
 * Class DoctrineListener
 * @package Ekyna\Component\Resource\Bridge\Symfony\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineListener extends Listener
{
    private ConfigManager               $configManager;
    private PersistenceTrackerInterface $tracker;
    private string                      $resourceId;

    public function configure(
        ConfigManager               $configManager,
        PersistenceTrackerInterface $tracker,
        string                      $resourceId
    ): void {
        $this->configManager = $configManager;
        $this->tracker = $tracker;
        $this->resourceId = $resourceId;
    }

    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        /** @var ResourceInterface $entity */
        $entity = $eventArgs->getObject();

        if (!$this->objectPersister->handlesObject($entity)) {
            return;
        }

        $configuration = $this->configManager->getIndexConfiguration($this->resourceId);

        $mapping = $configuration->getMapping()['properties'];

        $properties = array_map(
            fn(string $k) => u($k)->camel(),
            array_keys($mapping)
        );

        $changeSet = $this->tracker->getChangeSet($entity, $properties);

        if (empty($changeSet)) {
            return;
        }

        parent::postUpdate($eventArgs);
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Ekyna\Component\Resource\Behavior\BehaviorExecutorInterface;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\EntityListener;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityListenerTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM\Listener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EntityListenerTest extends TestCase
{
    /** @var PersistenceEventQueueInterface|MockObject|null */
    private ?MockObject $eventQueue = null;
    /** @var BehaviorExecutorInterface|MockObject|null */
    private ?MockObject     $behaviorExecutor = null;
    private ?EntityListener $entityListener   = null;

    protected function setUp(): void
    {
        $this->eventQueue = $this->createMock(PersistenceEventQueueInterface::class);
        $this->behaviorExecutor = $this->createMock(BehaviorExecutorInterface::class);
        $this->entityListener = new EntityListener($this->eventQueue, $this->behaviorExecutor);
    }

    protected function tearDown(): void
    {
        $this->eventQueue = null;
        $this->behaviorExecutor = null;
        $this->entityListener = null;
    }

    public function testOnFlushWithEntityInsertions()
    {
        $entity = $this->createMock(ResourceInterface::class);

        $event = $this->createFlushEvent([
            'getScheduledEntityInsertions' => [$entity],
        ]);

        $this->behaviorExecutor->expects(self::once())->method('execute')->with($entity, Behaviors::INSERT);

        $this->eventQueue->expects(self::any())->method('setOpened')->withConsecutive([true], [false]);
        $this->eventQueue->expects(self::once())->method('scheduleInsert')->with($entity);
        $this->eventQueue->expects(self::once())->method('flush');

        $this->entityListener->onFlush($event);
    }

    public function testOnFlushWithEntityUpdates()
    {
        $entity = $this->createMock(ResourceInterface::class);

        $event = $this->createFlushEvent([
            'getScheduledEntityUpdates' => [$entity],
        ]);

        $this->behaviorExecutor->expects(self::once())->method('execute')->with($entity, Behaviors::UPDATE);

        $this->eventQueue->expects(self::any())->method('setOpened')->withConsecutive([true], [false]);
        $this->eventQueue->expects(self::once())->method('scheduleUpdate')->with($entity);
        $this->eventQueue->expects(self::once())->method('flush');

        $this->entityListener->onFlush($event);
    }

    public function testOnFlushWithEntityDeletions()
    {
        $entity = $this->createMock(ResourceInterface::class);

        $event = $this->createFlushEvent([
            'getScheduledEntityDeletions' => [$entity],
        ]);

        $this->behaviorExecutor->expects(self::once())->method('execute')->with($entity, Behaviors::DELETE);

        $this->eventQueue->expects(self::any())->method('setOpened')->withConsecutive([true], [false]);
        $this->eventQueue->expects(self::once())->method('scheduleDelete')->with($entity);
        $this->eventQueue->expects(self::once())->method('flush');

        $this->entityListener->onFlush($event);
    }

    public function testOnFlushWithCollectionUpdates()
    {
        $managedEntity = $this->createMock(ResourceInterface::class);
        $managedEntity->method('getId')->willReturn(1);
        $notManagedEntity = $this->createMock(ResourceInterface::class);
        $notManagedEntity->method('getId')->willReturn(null);

        $event = $this->createFlushEvent([
            'getScheduledCollectionUpdates' => [[new ArrayCollection([$managedEntity, $notManagedEntity])]],
        ]);

        $this
            ->behaviorExecutor
            ->expects(self::any())
            ->method('execute')
            ->withConsecutive(
                [$managedEntity, Behaviors::UPDATE],
                [$notManagedEntity, Behaviors::INSERT]
            );

        $this->eventQueue->expects(self::any())->method('setOpened')->withConsecutive([true], [false]);
        $this->eventQueue->expects(self::once())->method('scheduleUpdate')->with($managedEntity);
        $this->eventQueue->expects(self::once())->method('scheduleInsert')->with($notManagedEntity);
        $this->eventQueue->expects(self::once())->method('flush');

        $this->entityListener->onFlush($event);
    }

    public function testOnFlushWithCollectionDeletions()
    {
        $entity = $this->createMock(ResourceInterface::class);

        $event = $this->createFlushEvent([
            'getScheduledCollectionDeletions' => [[new ArrayCollection([$entity])]],
        ]);

        $this->behaviorExecutor->expects(self::once())->method('execute')->with($entity, Behaviors::DELETE);

        $this->eventQueue->expects(self::any())->method('setOpened')->withConsecutive([true], [false]);
        $this->eventQueue->expects(self::once())->method('scheduleDelete')->with($entity);
        $this->eventQueue->expects(self::once())->method('flush');

        $this->entityListener->onFlush($event);
    }

    private function createFlushEvent(array $entities): OnFlushEventArgs
    {
        $entities = array_replace([
            'getScheduledEntityInsertions'    => [],
            'getScheduledEntityUpdates'       => [],
            'getScheduledEntityDeletions'     => [],
            'getScheduledCollectionUpdates'   => [],
            'getScheduledCollectionDeletions' => [],
        ], $entities);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        foreach ($entities as $method => $arguments) {
            $unitOfWork->method($method)->willReturn($arguments);
        }

        $manager = $this->createMock(EntityManager::class);
        $manager
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $event = $this->createMock(OnFlushEventArgs::class);
        $event
            ->method('getEntityManager')
            ->willReturn($manager);

        return $event;
    }
}

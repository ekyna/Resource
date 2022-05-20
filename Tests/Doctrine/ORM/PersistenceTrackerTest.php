<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM;

use Decimal\Decimal;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\PersistenceTracker;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function spl_object_hash;

/**
 * Class PersistenceTrackerTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PersistenceTrackerTest extends TestCase
{
    private MockObject         $registry;
    private PersistenceTracker $tracker;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    private function configureChangeSet(ResourceInterface $resource, array $changeSet): void
    {
        $tracker = $this->createMock(PersistenceTrackerInterface::class);
        $tracker
            ->method('getChangeSet')
            ->willReturn($changeSet);

        $this->tracker = new PersistenceTracker($this->registry);

        $rc = new ReflectionClass(PersistenceTracker::class);
        $rp = $rc->getProperty('changeSets');
        $rp->setAccessible(true);
        $rp->setValue($this->tracker, [
            spl_object_hash($resource) => $changeSet,
        ]);
    }

    public function testGetChangeSet(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->configureChangeSet($resource, [
            'string'  => ['foo', 'bar'],
            'bool'    => [false, true],
            'int'     => [12, 34],
            'decimal' => [new Decimal('0.5'), new Decimal('12.34')],
        ]);

        self::assertEquals([
            'string'  => ['foo', 'bar'],
            'bool'    => [false, true],
            'int'     => [12, 34],
            'decimal' => [new Decimal('0.5'), new Decimal('12.34')],
        ], $this->tracker->getChangeSet($resource, null));

        self::assertEquals(
            ['foo', 'bar'],
            $this->tracker->getChangeSet($resource, 'string')
        );

        self::assertEquals(
            [new Decimal('0.5'), new Decimal('12.34')],
            $this->tracker->getChangeSet($resource, 'decimal')
        );

        self::assertEquals([
            'bool' => [false, true],
            'int'  => [12, 34],
        ], $this->tracker->getChangeSet($resource, ['bool', 'int']));

        self::assertEquals([
            'string'  => ['foo', 'bar'],
            'decimal' => [new Decimal('0.5'), new Decimal('12.34')],
        ], $this->tracker->getChangeSet($resource, ['decimal', 'string']));
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM;

use Decimal\Decimal;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\PersistenceHelper;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;
use Ekyna\Component\Resource\Persistence\PersistenceTrackerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PersistenceHelperTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PersistenceHelperTest extends TestCase
{
    private MockObject        $registry;
    private MockObject        $queue;
    private PersistenceHelper $helper;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->queue = $this->createMock(PersistenceEventQueueInterface::class);
    }

    private function configureChangeSet(array $changeSet): void
    {
        $tracker = $this->createMock(PersistenceTrackerInterface::class);
        $tracker
            ->method('getChangeSet')
            ->willReturn($changeSet);

        $this->helper = new PersistenceHelper(
            $this->registry,
            $tracker,
            $this->queue
        );
    }

    public function testIsChanged(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->configureChangeSet([
            'string'  => ['foo', 'bar'],
            'bool'    => [false, true],
            'int'     => [12, 34],
            'decimal' => [new Decimal('0.5'), new Decimal('12.34')],
        ]);

        self::assertEquals(true, $this->helper->isChanged($resource, 'string'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'bool'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'int'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'decimal'));

        $this->configureChangeSet([
            'string'  => [null, ''],
            'bool'    => [false, null],
            'int'     => [null, 0],
            'decimal' => [null, new Decimal('12.34')],
        ]);

        self::assertEquals(true, $this->helper->isChanged($resource, 'string'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'bool'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'int'));
        self::assertEquals(true, $this->helper->isChanged($resource, 'decimal'));

        $this->configureChangeSet([
            'string' => [null, 'bar'],
        ]);

        self::assertEquals(true, $this->helper->isChanged($resource, 'string'));
        self::assertEquals(false, $this->helper->isChanged($resource, 'bool'));
        // TODO with array
    }

    public function testIsChangedFrom(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->configureChangeSet(['foo', 'bar']);
        self::assertEquals(true, $this->helper->isChangedFrom($resource, 'property', 'foo'));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', 'bar'));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', null));

        $this->configureChangeSet([false, null]);
        self::assertEquals(true, $this->helper->isChangedFrom($resource, 'property', false));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', true));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', null));

        $this->configureChangeSet([0, null]);
        self::assertEquals(true, $this->helper->isChangedFrom($resource, 'property', 0));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', false));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', null));

        $this->configureChangeSet([new Decimal('0.5'), new Decimal('12.34')]);
        self::assertEquals(true, $this->helper->isChangedFrom($resource, 'property', new Decimal('0.5')));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', '0.5'));
        self::assertEquals(false, $this->helper->isChangedFrom($resource, 'property', null));
    }

    public function testIsChangedTo(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->configureChangeSet(['foo', 'bar']);
        self::assertEquals(true, $this->helper->isChangedTo($resource, 'property', 'bar'));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', 'foo'));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', null));

        $this->configureChangeSet([false, null]);
        self::assertEquals(true, $this->helper->isChangedTo($resource, 'property', null));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', true));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', false));

        $this->configureChangeSet([null, 0]);
        self::assertEquals(true, $this->helper->isChangedTo($resource, 'property', 0));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', false));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', null));

        $this->configureChangeSet([new Decimal('0.5'), new Decimal('12.34')]);
        self::assertEquals(true, $this->helper->isChangedTo($resource, 'property', new Decimal('12.34')));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', '12.34'));
        self::assertEquals(false, $this->helper->isChangedTo($resource, 'property', null));
    }

    public function testIsChangedFromTo(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->configureChangeSet(['foo', 'bar']);
        self::assertEquals(true, $this->helper->isChangedFromTo($resource, 'property', 'foo', 'bar'));
        self::assertEquals(false, $this->helper->isChangedFromTo($resource, 'property', 'foo', false));
        self::assertEquals(false, $this->helper->isChangedFromTo($resource, 'property', null, 'bar'));
    }
}

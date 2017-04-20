<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Behavior;

use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Behavior\BehaviorRegistry;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Class BehaviorRegistryTest
 * @package Ekyna\Component\Resource\Tests\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BehaviorRegistryTest extends TestCase
{
    /**
     * @var MockObject|ContainerInterface
     */
    private $locator;

    private ?BehaviorRegistry $registry;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ContainerInterface::class);
        $this->registry = new BehaviorRegistry($this->locator, []);
    }

    protected function tearDown(): void
    {
        $this->locator = null;
        $this->registry = null;
    }

    public function testHasWhenBehaviorNotExists()
    {
        $this
            ->locator
            ->method('has')
            ->with('foo')
            ->willReturn(false);

        self::assertFalse($this->registry->hasBehavior('foo'));
    }

    public function testHasWhenBehaviorExists()
    {
        $this
            ->locator
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        self::assertTrue($this->registry->hasBehavior('foo'));
    }

    public function testGetWhenBehaviorNotExists()
    {
        $this
            ->locator
            ->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No resource behavior registered under the name 'foo'.");

        $this->registry->getBehavior('foo');
    }

    public function testGetWhenBehaviorExists()
    {
        $behavior = $this->createMock(BehaviorInterface::class);

        $this
            ->locator
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $this
            ->locator
            ->method('get')
            ->with('foo')
            ->willReturn($behavior);

        self::assertEquals($behavior, $this->registry->getBehavior('foo'));
    }
}

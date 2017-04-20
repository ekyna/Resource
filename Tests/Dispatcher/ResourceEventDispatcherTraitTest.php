<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Dispatcher;

use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherTrait;
use Ekyna\Component\Resource\Event\EventQueueInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourceEventDispatcherTraitTest
 * @package Ekyna\Component\Resource\Tests\Dispatcher
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceEventDispatcherTraitTest extends TestCase
{
    /**
     * @var MockObject|ResourceRegistryInterface
     */
    private $registry;

    /**
     * @var MockObject|EventQueueInterface
     */
    private $queue;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ResourceRegistryInterface::class);
        $this->queue = $this->createMock(EventQueueInterface::class);

        $this->dispatcher = new class {
            use ResourceEventDispatcherTrait;
        };
        $this->dispatcher->setConfigurationRegistry($this->registry);
        $this->dispatcher->setEventQueue($this->queue);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
        $this->queue = null;
        $this->dispatcher = null;
    }

    public function testScheduleEvent(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->queue
            ->expects(self::once())
            ->method('scheduleEvent')
            ->with($resource, 'foo');

        $this->dispatcher->scheduleEvent($resource, 'foo');
    }

    public function testCreateResourceEventWithRegisteredResource(): void
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->method('getEventClass')
            ->willReturn(ResourceEvent::class);

        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->registry
            ->method('find')
            ->with($resource)
            ->willReturn($config);

        $event = $this->dispatcher->createResourceEvent($resource);

        self::assertInstanceOf(ResourceEvent::class, $event);
        self::assertEquals($resource, $event->getResource());
    }

    public function testCreateResourceEventWithUnregisteredResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->registry
            ->method('find')
            ->with($resource, true)
            ->willThrowException($this->createMock(NotFoundConfigurationException::class));

        $this->expectException(NotFoundConfigurationException::class);

        $this->dispatcher->createResourceEvent($resource);
    }

    public function testCreateResourceEventWithUnregisteredResourceAndNoException(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->registry
            ->method('find')
            ->with($resource, false)
            ->willReturn(null);

        self::assertNull($this->dispatcher->createResourceEvent($resource, false));
    }

    public function testGetResourceEventNameWithRegisteredResource(): void
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->method('getEventName')
            ->with('suffix')
            ->willReturn('foo.suffix');

        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->registry
            ->method('find')
            ->with($resource)
            ->willReturn($config);

        self::assertEquals('foo.suffix', $this->dispatcher->getResourceEventName($resource, 'suffix'));
    }

    public function testGetResourceEventNameWithUnregisteredResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this
            ->registry
            ->method('find')
            ->with($resource)
            ->willReturn(null);

        self::assertNull($this->dispatcher->getResourceEventName($resource, 'suffix'));
    }
}

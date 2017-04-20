<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Behavior;

use Acme\Resource\Entity\Post;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Resource\Behavior\BehaviorExecutor;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Behavior\BehaviorRegistryInterface;
use Ekyna\Component\Resource\Config\BehaviorConfig;
use Ekyna\Component\Resource\Config\Registry\BehaviorRegistryInterface as BehaviorConfigRegistry;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface as ResourceConfigRegistry;
use Ekyna\Component\Resource\Config\ResourceConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class BehaviorExecutorTest
 * @package Ekyna\Component\Resource\Tests\Behavior
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BehaviorExecutorTest extends TestCase
{
    /**
     * @var MockObject|ResourceConfigRegistry
     */
    private $resourceRegistry;

    /**
     * @var MockObject|BehaviorConfigRegistry
     */
    private $configRegistry;

    /**
     * @var MockObject|BehaviorRegistryInterface
     */
    private $behaviorRegistry;

    private ?BehaviorExecutor $executor;

    protected function setUp(): void
    {
        $this->resourceRegistry = $this->createMock(ResourceConfigRegistry::class);
        $this->configRegistry = $this->createMock(BehaviorConfigRegistry::class);
        $this->behaviorRegistry = $this->createMock(BehaviorRegistryInterface::class);

        $this->executor = new BehaviorExecutor(
            $this->resourceRegistry,
            $this->configRegistry,
            $this->behaviorRegistry
        );
    }

    protected function tearDown(): void
    {
        $this->resourceRegistry = null;
        $this->configRegistry = null;
        $this->behaviorRegistry = null;
        $this->executor = null;
    }

    public function testExecute()
    {
        $post = $this->createMock(Post::class);

        $rConfig = $this->createMock(ResourceConfig::class);
        $rConfig
            ->method('getBehaviors')
            ->willReturn(['foo_behavior' => []]);

        $this
            ->resourceRegistry
            ->method('find')
            ->with($post)
            ->willReturn($rConfig);

        $bConfig = $this->createMock(BehaviorConfig::class);
        $bConfig
            ->method('getOperations')
            ->willReturn(['onInsert']);

        $this
            ->configRegistry
            ->method('find')
            ->with('foo_behavior')
            ->willReturn($bConfig);

        $behavior = $this->createMock(BehaviorInterface::class);
        $behavior
            ->expects(self::once())
            ->method('onInsert')
            ->with($post, []);

        $this
            ->behaviorRegistry
            ->method('getBehavior')
            ->with('foo_behavior')
            ->willReturn($behavior);

        $this->executor->execute($post, 'onInsert');
    }

    public function testExecuteWithOptions()
    {
        $post = $this->createMock(Post::class);

        $rConfig = $this->createMock(ResourceConfig::class);
        $rConfig
            ->method('getBehaviors')
            ->willReturn([
                'foo_behavior' => [
                    'array' => [
                        'data' => 'Luke',
                    ],
                ],
            ]);

        $this
            ->resourceRegistry
            ->method('find')
            ->with($post)
            ->willReturn($rConfig);

        $bConfig = $this->createMock(BehaviorConfig::class);
        $bConfig
            ->method('getOperations')
            ->willReturn(['onUpdate']);
        $bConfig
            ->method('getDefaultOptions')
            ->willReturn([
                'foo'   => 'bar',
                'array' => [
                    'data' => 'datum',
                ],
            ]);

        $this
            ->configRegistry
            ->method('find')
            ->with('foo_behavior')
            ->willReturn($bConfig);

        $behavior = $this->createMock(BehaviorInterface::class);
        $behavior
            ->expects(self::once())
            ->method('onUpdate')
            ->with($post, [
                'foo'   => 'bar',
                'array' => [
                    'data' => 'Luke',
                ],
            ]);

        $this
            ->behaviorRegistry
            ->method('getBehavior')
            ->with('foo_behavior')
            ->willReturn($behavior);

        $this->executor->execute($post, 'onUpdate');
    }

    public function testMetadata()
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getName')
            ->willReturn(Post::class);

        $rConfig = $this->createMock(ResourceConfig::class);
        $rConfig
            ->method('getBehaviors')
            ->willReturn(['foo_behavior' => []]);

        $this
            ->resourceRegistry
            ->method('find')
            ->with(Post::class)
            ->willReturn($rConfig);

        $bConfig = $this->createMock(BehaviorConfig::class);
        $bConfig
            ->method('getOperations')
            ->willReturn(['onMetadata']);

        $this
            ->configRegistry
            ->method('find')
            ->with('foo_behavior')
            ->willReturn($bConfig);

        $behavior = $this->createMock(BehaviorInterface::class);
        $behavior
            ->expects(self::once())
            ->method('onMetadata')
            ->with($metadata);

        $this
            ->behaviorRegistry
            ->method('getBehavior')
            ->with('foo_behavior')
            ->willReturn($behavior);

        $this->executor->metadata($metadata);
    }
}

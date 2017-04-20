<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM\Listener;

use Acme\Resource\Entity\Post;
use Ekyna\Component\Resource\Behavior\BehaviorExecutorInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\LoadMetadataListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class LoadMetadataListenerTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM\Listener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoadMetadataListenerTest extends TestCase
{
    /** @var BehaviorExecutorInterface|MockObject|null */
    private ?MockObject           $behaviorExecutor = null;
    private ?LoadMetadataListener $metadataListener = null;

    protected function setUp(): void
    {
        $this->behaviorExecutor = $this->createMock(BehaviorExecutorInterface::class);
        $this->metadataListener = new LoadMetadataListener($this->behaviorExecutor, [
            Post::class,
        ]);
    }

    protected function tearDown(): void
    {
        $this->behaviorExecutor = null;
        $this->metadataListener = null;
    }

    public function testLoadClassMetadata(): void
    {
        $this->markTestIncomplete(); // TODO
    }
}

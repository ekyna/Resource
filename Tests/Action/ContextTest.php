<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Action;

use Acme\Resource\Entity\Post;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Config\ResourceConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class ContextTest
 * @package Ekyna\Component\Resource\Tests\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ContextTest extends TestCase
{
    public function testGetConfig(): void
    {
        $config = $this->createMock(ResourceConfig::class);

        $context = new Context($config);

        self::assertSame($config, $context->getConfig());
    }

    public function testSetGetResource(): void
    {
        $context = new Context($this->createMock(ResourceConfig::class));

        $post = $this->createMock(Post::class);

        $context->setResource($post);

        self::assertSame($post, $context->getResource());
    }

    public function testSetGetParent(): void
    {
        $context = new Context($this->createMock(ResourceConfig::class));
        self::assertNull($context->getParent());

        $parent = new Context($this->createMock(ResourceConfig::class));

        $context->setParent($parent);

        self::assertSame($parent, $context->getParent());
    }

    public function testGetParentResource(): void
    {
        $context = new Context($this->createMock(ResourceConfig::class));
        self::assertNull($context->getParentResource());

        $parent = new Context($this->createMock(ResourceConfig::class));
        $post = $this->createMock(Post::class);
        $parent->setResource($post);

        $context->setParent($parent);

        self::assertSame($post, $context->getParentResource());
    }
}

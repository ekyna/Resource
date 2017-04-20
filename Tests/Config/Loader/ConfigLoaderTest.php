<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Loader;

use Acme\Resource\Action;
use Acme\Resource\Behavior;
use Acme\Resource\Entity;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Class ConfigLoaderTest
 * @package Ekyna\Component\Resource\Tests\Config\Loader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigLoaderTest extends TestCase
{
    private ?ConfigLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new ConfigLoader();
    }

    protected function tearDown(): void
    {
        $this->loader = null;
    }

    public function testAddFile(): void
    {
        $resource = new FileResource(__DIR__ . '/../../Fixtures/config/empty.yml');

        $this->loader->addFile($resource);

        self::assertSame([$resource], $this->loader->getFiles());
    }

    public function testAddFileWhileNotTracking(): void
    {
        $resource = new FileResource(__DIR__ . '/../../Fixtures/config/empty.yml');

        $this->loader->setFileTracking(false);
        $this->loader->addFile($resource);

        self::assertSame([], $this->loader->getFiles());
    }

    public function testSetFileTracking(): void
    {
        $this->loader->setFileTracking(false);
        self::assertFalse($this->loader->isTrackingFiles());

        $this->loader->setFileTracking(true);
        self::assertTrue($this->loader->isTrackingFiles());
    }

    public function testPermissions(): void
    {
        // Add
        $data = ['label' => 'foo'];
        $this->loader->addPermission('foo', $data);
        self::assertSame($data, $this->loader->getPermission('foo'));
        self::assertSame(['foo' => $data], $this->loader->getPermissions());

        // Override
        $data = ['label' => 'changed'];
        $this->loader->addPermission('foo', $data);
        self::assertSame($data, $this->loader->getPermission('foo'));
        self::assertSame(['foo' => $data], $this->loader->getPermissions());

        // Not found
        $this->expectException(NotFoundConfigurationException::class);
        $this->loader->getPermission('bar');
    }

    public function testNamespaces(): void
    {
        // Add
        $data = ['prefix' => '/foo'];
        $this->loader->addNamespace('foo', $data);
        self::assertSame($data, $this->loader->getNamespace('foo'));
        self::assertSame(['foo' => $data], $this->loader->getNamespaces());

        // Override
        $data = ['prefix' => '/changed'];
        $this->loader->addNamespace('foo', $data);
        self::assertSame($data, $this->loader->getNamespace('foo'));
        self::assertSame(['foo' => $data], $this->loader->getNamespaces());

        // Not found
        $this->expectException(NotFoundConfigurationException::class);
        $this->loader->getNamespace('bar');
    }

    public function testActions(): void
    {
        // Add
        $data = ['class' => Action\FooAction::class];
        $this->loader->addAction('foo', $data);
        self::assertSame($data, $this->loader->getAction('foo'));
        self::assertSame(['foo' => $data], $this->loader->getActions());

        // Override
        $data = ['class' => Action\BarAction::class];
        $this->loader->addAction('foo', $data);
        self::assertSame($data, $this->loader->getAction('foo'));
        self::assertSame(['foo' => $data], $this->loader->getActions());

        // Not found
        $this->expectException(NotFoundConfigurationException::class);
        $this->loader->getAction('bar');
    }

    public function testBehaviors(): void
    {
        // Add
        $data = ['class' => Behavior\FooBehavior::class];
        $this->loader->addBehavior('foo', $data);
        self::assertSame($data, $this->loader->getBehavior('foo'));
        self::assertSame(['foo' => $data], $this->loader->getBehaviors());

        // Override
        $data = ['class' => Behavior\BarBehavior::class];
        $this->loader->addBehavior('foo', $data);
        self::assertSame($data, $this->loader->getBehavior('foo'));
        self::assertSame(['foo' => $data], $this->loader->getBehaviors());

        // Not found
        $this->expectException(NotFoundConfigurationException::class);
        $this->loader->getBehavior('bar');
    }

    public function testResources(): void
    {
        // Add
        $data = [
            'namespace'  => 'acme',
            'entity'     => [
                'class' => Entity\Post::class,
            ],
            'interfaces' => [],
        ];
        $this->loader->addResource('foo', $data);
        self::assertSame($data, $this->loader->getResource('foo'));
        self::assertSame(['foo' => $data], $this->loader->getResources());

        // Override
        $this->loader->addResource('foo', [
            'entity' => [
                'interface' => Entity\CommentInterface::class,
                'class'     => Entity\Comment::class,
            ],
        ]);
        $result = [
            'namespace' => 'acme',
            'entity'    => [
                'class'     => Entity\Comment::class,
                'interface' => Entity\CommentInterface::class,
            ],
            'interfaces' => [
                Entity\CommentInterface::class
            ],
        ];
        self::assertSame($result, $this->loader->getResource('foo'));
        self::assertSame(['foo' => $result], $this->loader->getResources());

        self::assertSame($result, $this->loader->getResource('foo'));
        self::assertSame(['foo' => $result], $this->loader->getResources());

        // Not found
        $this->expectException(NotFoundConfigurationException::class);
        $this->loader->getResource('bar');
    }
}

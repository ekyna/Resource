<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Acme\Resource\Action\BarAction;
use Acme\Resource\Action\FooAction;
use Acme\Resource\Entity\Bar;
use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\Registry\ActionRegistry;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;

/**
 * Class ActionRegistryTest
 * @package Ekyna\Component\Resource\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionRegistryTestCase extends AbstractRegistryTestCase
{
    private const FOO_DATA = [
        'class' => FooAction::class,
    ];
    private const BAR_DATA = [
        'class' => BarAction::class,
    ];

    private ?ActionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ActionRegistry([], [
            'foo' => self::FOO_DATA,
            'bar' => self::BAR_DATA,
        ], [
            FooAction::class => 'foo',
            BarAction::class => 'bar',
        ], ActionConfig::class);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    public function testFind(): void
    {
        self::assertEquals(
            new ActionConfig('foo', self::FOO_DATA),
            $this->registry->find('foo')
        );

        self::assertEquals(
            new ActionConfig('bar', self::BAR_DATA),
            $this->registry->find('bar')
        );

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find('unknown');
    }

    public function testFindCached(): void
    {
        $config = new ActionConfig('foo', self::FOO_DATA);

        $cache = $this->mockCacheGet($config);
        $this->setCache($this->registry, $cache);

        $this->registry->find('foo');
    }

    public function testFindByClass(): void
    {
        // Non cached
        self::assertEquals(
            new ActionConfig('foo', self::FOO_DATA),
            $this->registry->find(FooAction::class)
        );

        // Cached
        self::assertEquals(
            new ActionConfig('foo', self::FOO_DATA),
            $this->registry->find(FooAction::class)
        );

        self::assertEquals(
            new ActionConfig('bar', self::BAR_DATA),
            $this->registry->find(BarAction::class)
        );

        self::assertNull($this->registry->find(Bar::class, false));

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find(Bar::class);
    }

    public function testAll(): void
    {
        $expected = [
            'foo' => new ActionConfig('foo', self::FOO_DATA),
            'bar' => new ActionConfig('bar', self::BAR_DATA),
        ];

        $actual = iterator_to_array($this->registry->all());

        self::assertEquals($expected, $actual);
    }
}

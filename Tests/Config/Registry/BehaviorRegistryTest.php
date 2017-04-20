<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Acme\Resource\Behavior\BarBehavior;
use Acme\Resource\Behavior\FooBehavior;
use Acme\Resource\Entity\Bar;
use Ekyna\Component\Resource\Config\BehaviorConfig;
use Ekyna\Component\Resource\Config\Registry\BehaviorRegistry;
use Ekyna\Component\Resource\Config\Registry\Cache;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * Class BehaviorRegistryTest
 * @package Ekyna\Component\Resource\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BehaviorRegistryTest extends TestCase
{
    private const FOO_DATA = [
        'class' => FooBehavior::class,
    ];
    private const BAR_DATA = [
        'class' => BarBehavior::class,
    ];

    private ?BehaviorRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new BehaviorRegistry([], [
            'foo' => self::FOO_DATA,
            'bar' => self::BAR_DATA,
        ], [
            FooBehavior::class => 'foo',
            BarBehavior::class => 'bar',
        ], BehaviorConfig::class);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    public function testFind(): void
    {
        self::assertEquals(
            new BehaviorConfig('foo', self::FOO_DATA),
            $this->registry->find('foo')
        );

        self::assertEquals(
            new BehaviorConfig('bar', self::BAR_DATA),
            $this->registry->find('bar')
        );

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find('unknown');
    }

    public function testGetCached(): void
    {
        $config = new BehaviorConfig('foo', self::FOO_DATA);

        $cache = $this->createMock(Cache::class);
        $cache->method('has')->with('foo')->willReturn(true);
        $cache->method('get')->with('foo')->willReturn($config);

        $cache->expects(self::once())->method('get');

        $this->registry->setCache($cache);

        $this->registry->find('foo');
    }

    public function testAll(): void
    {
        $expected = [
            'foo' => new BehaviorConfig('foo', self::FOO_DATA),
            'bar' => new BehaviorConfig('bar', self::BAR_DATA),
        ];

        $actual = iterator_to_array($this->registry->all());

        self::assertEquals($expected, $actual);
    }

    public function testFindByClass(): void
    {
        // Non cached
        self::assertEquals(
            new BehaviorConfig('foo', self::FOO_DATA),
            $this->registry->find(FooBehavior::class)
        );

        // Cached
        // TODO assert cache is used
        self::assertEquals(
            new BehaviorConfig('foo', self::FOO_DATA),
            $this->registry->find(FooBehavior::class)
        );

        self::assertEquals(
            new BehaviorConfig('bar', self::BAR_DATA),
            $this->registry->find(BarBehavior::class)
        );

        self::assertNull($this->registry->find(Bar::class, false));

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find(Bar::class);
    }
}

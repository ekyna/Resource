<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Ekyna\Component\Resource\Config\NamespaceConfig;
use Ekyna\Component\Resource\Config\Registry\NamespaceRegistry;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ekyna\Component\Resource\Config\Registry\NamespaceRegistry
 */
class NamespaceRegistryTest extends TestCase
{
    private const FOO_DATA = [
        'label'  => 'foo',
        'prefix' => '/foo',
        'domain' => 'Acme',
    ];
    private const BAR_DATA = [
        'label'  => 'bar.label',
    ];

    private ?NamespaceRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new NamespaceRegistry([], [
            'foo' => self::FOO_DATA,
            'bar' => self::BAR_DATA,
        ], [], NamespaceConfig::class);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    public function testFind(): void
    {
        self::assertEquals(
            new NamespaceConfig('foo', self::FOO_DATA),
            $this->registry->find('foo')
        );

        self::assertEquals(
            new NamespaceConfig('bar', self::BAR_DATA),
            $this->registry->find('bar')
        );

        $this->expectException(NotFoundConfigurationException::class);

        $this->registry->find('unknown');
    }

    public function testAll(): void
    {
        $expected = [
            'foo' => new NamespaceConfig('foo', self::FOO_DATA),
            'bar' => new NamespaceConfig('bar', self::BAR_DATA),
        ];

        $actual = iterator_to_array($this->registry->all());

        self::assertEquals($expected, $actual);
    }
}

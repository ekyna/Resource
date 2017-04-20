<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Ekyna\Component\Resource\Config\PermissionConfig;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistry;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ekyna\Component\Resource\Config\Registry\PermissionRegistry
 */
class PermissionRegistryTest extends TestCase
{
    private const FOO_DATA = [
        'label'  => 'foo.label',
        'domain' => 'Acme',
    ];
    private const BAR_DATA = [
        'label'  => 'bar.label',
    ];

    private ?PermissionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new PermissionRegistry([], [
            'foo' => self::FOO_DATA,
            'bar' => self::BAR_DATA,
        ], [], PermissionConfig::class);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    public function testGet(): void
    {
        self::assertEquals(
            new PermissionConfig('foo', self::FOO_DATA),
            $this->registry->find('foo')
        );

        self::assertEquals(
            new PermissionConfig('bar', self::BAR_DATA),
            $this->registry->find('bar')
        );

        $this->expectException(NotFoundConfigurationException::class);

        $this->registry->find('unknown');
    }

    public function testAll(): void
    {
        $expected = [
            'foo' => new PermissionConfig('foo', self::FOO_DATA),
            'bar' => new PermissionConfig('bar', self::BAR_DATA),
        ];

        $actual = iterator_to_array($this->registry->all());

        self::assertEquals($expected, $actual);
    }
}

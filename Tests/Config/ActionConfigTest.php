<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config;

use Acme\Resource\Action\FooAction;
use Ekyna\Component\Resource\Config\ActionConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class ActionConfigTest
 * @package Ekyna\Component\Resource\Tests\Config
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionConfigTest extends TestCase
{
    public function testBasic(): void
    {
        $config = $this->getBasicConfig();

        self::assertEquals('foo_name', $config->getName());
        self::assertEquals(FooAction::class, $config->getClass());
        self::assertEquals('foo_route', $config->getRoute());

        self::assertNull($config->getButton());
        self::assertEquals([], $config->getPermissions());
        self::assertEquals([], $config->getDefaultOptions());
    }

    public function testAdvanced(): void
    {
        $config = $this->getAdvancedConfig();

        self::assertEquals('acme_foo', $config->getName());
        self::assertEquals(FooAction::class, $config->getClass());
        self::assertEquals('foo_route', $config->getRoute());

        self::assertEquals(['foo'], $config->getPermissions());

        self::assertEquals([
            'label' => 'Foo',
            'theme' => 'default',
            'icon'  => 'check',
        ], $config->getButton());

        self::assertEquals([
            'template' => 'foo.html.twig'
        ], $config->getDefaultOptions());
    }

    private function getBasicConfig(): ActionConfig
    {
        return new ActionConfig('foo_name', [
            'class' => FooAction::class,
            'route' => 'foo_route',
        ]);
    }

    private function getAdvancedConfig(): ActionConfig
    {
        return new ActionConfig('acme_foo', [
            'class'       => FooAction::class,
            'route'       => 'foo_route',
            'permissions' => ['foo'],
            'button'      => [
                'label' => 'Foo',
                'theme' => 'default',
                'icon'  => 'check',
            ],
            'options'     => [
                'template' => 'foo.html.twig',
            ],
        ]);
    }
}

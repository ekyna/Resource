<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config;

use Acme\Resource\Behavior\FooBehavior;
use Acme\Resource\Behavior\FooInterface;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Config\BehaviorConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class BehaviorConfigTest
 * @package Ekyna\Component\Resource\Tests\Config
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BehaviorConfigTest extends TestCase
{
    public function testBasic(): void
    {
        $config = $this->getBasicConfig();

        self::assertEquals('foo_name', $config->getName());
        self::assertEquals(FooBehavior::class, $config->getClass());
        self::assertEquals(FooInterface::class, $config->getInterface());
        self::assertEquals([Behaviors::INSERT], $config->getOperations());
        self::assertEquals([], $config->getDefaultOptions());
    }

    public function testAdvanced(): void
    {
        $config = $this->getAdvancedConfig();

        self::assertEquals('bar_name', $config->getName());
        self::assertEquals(FooBehavior::class, $config->getClass());
        self::assertEquals(FooInterface::class, $config->getInterface());
        self::assertEquals([Behaviors::METADATA], $config->getOperations());
        self::assertEquals(['foo' => 'bar'], $config->getDefaultOptions());
    }

    private function getBasicConfig(): BehaviorConfig
    {
        return new BehaviorConfig('foo_name', [
            'class'      => FooBehavior::class,
            'interface'  => FooInterface::class,
            'operations' => [
                Behaviors::INSERT,
            ],
        ]);
    }

    private function getAdvancedConfig(): BehaviorConfig
    {
        return new BehaviorConfig('bar_name', [
            'class'      => FooBehavior::class,
            'interface'  => FooInterface::class,
            'operations' => [
                Behaviors::METADATA,
            ],
            'options'    => [
                'foo' => 'bar',
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Ekyna\Component\Resource\Config\AbstractConfig;
use Ekyna\Component\Resource\Config\Registry\Cache;
use PHPUnit\Framework\TestCase;

/**
 * Class CacheTest
 * @package Ekyna\Component\Resource\Tests\Config\Registry
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CacheTest extends TestCase
{
    public function testHas(): void
    {
        $cache = new Cache();

        self::assertFalse($cache->has('foo'));

        $cache->set('foo', $this->createMock(AbstractConfig::class));

        self::assertTrue($cache->has('foo'));
    }

    public function testSetGet(): void
    {
        $config = $this->createMock(AbstractConfig::class);

        $cache = new Cache();

        $cache->set('foo', $config);

        self::assertEquals($config, $cache->get('foo'));
    }
}

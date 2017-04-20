<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Factory;

use Ekyna\Component\Resource\Config\Factory\Cache;
use Ekyna\Component\Resource\Config\Registry\AbstractRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Class CacheTest
 * @package Ekyna\Component\Resource\Tests\Config\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CacheTest extends TestCase
{
    public function test_has(): void
    {
        $cache = new Cache();

        self::assertFalse($cache->has('foo'));

        $cache->set('foo', $this->createMock(AbstractRegistry::class));

        self::assertTrue($cache->has('foo'));
    }

    public function test_set_get(): void
    {
        $registry = $this->createMock(AbstractRegistry::class);

        $cache = new Cache();

        $cache->set('foo', $registry);

        self::assertEquals($registry, $cache->get('foo'));
    }
}

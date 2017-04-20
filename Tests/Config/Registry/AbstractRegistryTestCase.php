<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Ekyna\Component\Resource\Config\AbstractConfig;
use Ekyna\Component\Resource\Config\Registry\AbstractRegistry;
use Ekyna\Component\Resource\Config\Registry\Cache;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class AbstractRegistryTest
 * @package Ekyna\Component\Resource\Tests\Config\Registry
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractRegistryTestCase extends TestCase
{
    protected function setCache(AbstractRegistry $registry, Cache $cache): void
    {
        $classReflexion = new ReflectionClass(AbstractRegistry::class);
        $propertyReflexion = $classReflexion->getProperty('cache');
        $propertyReflexion->setAccessible(true);
        $propertyReflexion->setValue($registry, $cache);
        $propertyReflexion->setAccessible(false);
    }

    protected function mockCacheGet(AbstractConfig $config): Cache
    {
        $cache = $this->createMock(Cache::class);

        $cache->method('has')->with('foo')->willReturn(true);
        $cache->method('get')->with('foo')->willReturn($config);

        $cache->expects(self::once())->method('get');

        return $cache;
    }
}

<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Factory;

use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\BehaviorConfig;
use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\Factory\Cache;
use Ekyna\Component\Resource\Config\Factory\RegistryFactory;
use Ekyna\Component\Resource\Config\Loader\ChildrenLoader;
use Ekyna\Component\Resource\Config\NamespaceConfig;
use Ekyna\Component\Resource\Config\PermissionConfig;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\BehaviorRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\NamespaceRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistry;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Class RegistryFactoryTest
 * @package Ekyna\Component\Resource\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RegistryFactoryTest extends TestCase
{
    public function test_setCache_whileRegistriesAreBuilt()
    {
        $factory = $this->createFixturesFactory();

        $factory->getPermissionRegistry();

        $this->expectException(RuntimeException::class);

        $factory->setCache(new Cache());
    }

    public function test_getPermissionRegistry(): void
    {
        $cache = $this->createMock(Cache::class);

        $cache
            ->method('has')
            ->with(PermissionRegistryInterface::NAME)
            ->willReturn(false);

        $cache->expects(self::never())->method('get');

        $factory = $this
            ->createFixturesFactory()
            ->setCache($cache);

        $registry = $factory->getPermissionRegistry();

        self::assertInstanceOf(PermissionRegistryInterface::class, $registry);

        $expected = new PermissionConfig('foo', ['label' => 'foo.label', 'name' => 'foo', 'trans_domain' => null]);

        self::assertEquals($expected, $registry->find('foo'));
    }

    public function test_getPermissionRegistry_whenCached(): void
    {
        $cache = $this->createMock(Cache::class);

        $cache
            ->method('has')
            ->with(PermissionRegistryInterface::NAME)
            ->willReturn(true);

        $cache
            ->method('get')
            ->with(PermissionRegistryInterface::NAME)
            ->willReturn(new PermissionRegistry([], [], [], PermissionConfig::class));

        $factory = $this
            ->createFixturesFactory()
            ->setCache($cache);

        $cache->expects(self::once())->method('get');

        $registry = $factory->getPermissionRegistry();

        self::assertInstanceOf(PermissionRegistryInterface::class, $registry);
    }

    public function test_getNamespaceRegistry(): void
    {
        $registry = $this
            ->createFixturesFactory()
            ->getNamespaceRegistry();

        self::assertInstanceOf(NamespaceRegistryInterface::class, $registry);

        $expected = new NamespaceConfig('foo', [
            'prefix'       => '/foo',
            'name'         => 'foo',
            'trans_domain' => null,
            'label'        => null,
        ]);

        self::assertEquals($expected, $registry->find('foo'));
    }

    public function test_getActionRegistry(): void
    {
        $registry = $this
            ->createFixturesFactory()
            ->getActionRegistry();

        self::assertInstanceOf(ActionRegistryInterface::class, $registry);

        $expected = new ActionConfig('foo', [
            'permission' => 'foo',
            'options'    => [
                'expose' => false,
            ],
            'class'      => 'Acme\\Resource\\Action\\FooAction',
            'name'       => 'foo',
            'route'      => 'foo',
        ]);

        self::assertEquals($expected, $registry->find('foo'));
    }

    public function test_getBehaviorRegistry(): void
    {
        $registry = $this
            ->createFixturesFactory()
            ->getBehaviorRegistry();

        self::assertInstanceOf(BehaviorRegistryInterface::class, $registry);

        $expected = new BehaviorConfig('foo', [
            'class'      => 'Acme\\Resource\\Behavior\\FooBehavior',
            'name'       => 'foo',
            'interface'  => 'Acme\\Resource\\Behavior\\FooBehaviorInterface',
            'operations' => ['0' => 'onMetadata'],
            'options'    => [],
        ]);

        self::assertEquals($expected, $registry->find('foo'));
    }

    public function test_getResourceRegistry(): void
    {
        $registry = $this
            ->createFixturesFactory()
            ->getResourceRegistry();

        self::assertInstanceOf(ResourceRegistryInterface::class, $registry);

        $expected = new ResourceConfig('acme.category', [
            'entity'       => [
                'interface' => null,
                'class'     => 'Acme\\Resource\\Entity\\Category',
            ],
            'repository'   => [
                'interface' => null,
                'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Repository\\ResourceRepository',
            ],
            'manager'      => [
                'interface' => null,
                'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Manager\\ResourceManager',
            ],
            'factory'      => [
                'interface' => null,
                'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Factory\\ResourceFactory',
            ],
            'translation'  => null,
            'parent'       => null,
            'event'        => 'Ekyna\\Component\\Resource\\Event\\ResourceEvent',
            'actions'      => [],
            'behaviors'    => [],
            'permissions'  => [],
            'namespace'    => 'acme',
            'name'         => 'category',
            'trans_prefix' => null,
            'trans_domain' => null,
        ]);

        $expected->setChildrenLoader(ChildrenLoader::create($registry));

        self::assertEquals($expected, $registry->find('acme.category'));
    }

    private function createFixturesFactory(): RegistryFactory
    {
        return new RegistryFactory(Config::create(__DIR__ . '/../../Fixtures/cache'));
    }
}

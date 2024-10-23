<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Loader;

use Acme\Resource\Action\FooAction;
use Acme\Resource\Behavior\FooBehavior;
use Acme\Resource\Behavior\FooInterface;
use Acme\Resource\Entity\Comment;
use Acme\Resource\Entity\Post;
use Acme\Resource\Entity\PostInterface;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Config\Loader\YamlFileLoader;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

/**
 * Class YamlFileLoaderTest
 * @package Ekyna\Component\Resource\Tests\Config\Loader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class YamlFileLoaderTest extends TestCase
{
    /**
     * @var MockObject|ConfigLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->loader = $this
            ->getMockBuilder(ConfigLoader::class)
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->loader = null;
    }

    public function test_supports(): void
    {
        /** @var FileLocator $locator */
        $locator = $this->getMockBuilder(FileLocator::class)->getMock();
        $loader = new YamlFileLoader($this->loader, $locator);

        self::assertTrue($loader->supports('foo.yml'));
        self::assertTrue($loader->supports('foo.yaml'));
        self::assertFalse($loader->supports('foo.bar'));

        self::assertTrue($loader->supports('foo.yml', 'yaml'));
        self::assertTrue($loader->supports('foo.yaml', 'yaml'));
        self::assertFalse($loader->supports('foo.yml', 'foo'));
    }

    public function testLoadDoesNothingIfEmpty(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addBehavior');
        $this->loader->expects(self::never())->method('addResource');

        $this->createFixturesLoader()->load('empty.yml');
    }

    public function testLoadPermissionsWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/permission1.yml');
    }

    public function testLoadPermissionWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/permission2.yml');
    }

    public function testLoadValidPermission(): void
    {
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addBehavior');
        $this->loader->expects(self::never())->method('addResource');

        $this
            ->loader
            ->expects(self::once())
            ->method('addPermission')
            ->with('foo', [
                'name'  => 'foo',
                'label' => 'bar',
            ]);

        $this->createFixturesLoader()->load('permission.yml');
    }

    public function testLoadNamespacesWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/namespace1.yml');
    }

    public function testLoadNamespaceWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/namespace2.yml');
    }

    public function testLoadValidNamespace(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addBehavior');
        $this->loader->expects(self::never())->method('addResource');

        $this
            ->loader
            ->expects(self::once())
            ->method('addNamespace')
            ->with('acme', [
                'name'   => 'acme',
                'prefix' => '/acme',
            ]);

        $this->createFixturesLoader()->load('namespace.yml');
    }

    public function testLoadActionsWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/action1.yml');
    }

    public function testLoadActionWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/action2.yml');
    }

    public function testLoadValidAction(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addBehavior');
        $this->loader->expects(self::never())->method('addResource');

        $this
            ->loader
            ->expects(self::once())
            ->method('addAction')
            ->with('foo_action', [
                'name'        => 'foo_action',
                'class'       => FooAction::class,
                'route'       => 'foo_route',
                'permissions' => 'foo_perm',
                'options'     => ['foo' => 'bar'],
            ]);

        $this->createFixturesLoader()->load('action.yml');
    }

    public function testLoadBehaviorsWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/behavior1.yml');
    }

    public function testLoadBehaviorWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/behavior2.yml');
    }

    public function testLoadValidBehavior(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addResource');

        $this
            ->loader
            ->expects(self::once())
            ->method('addBehavior')
            ->with('foo_behavior', [
                'name'       => 'foo_behavior',
                'class'      => FooBehavior::class,
                'interface'  => FooInterface::class,
                'operations' => [Behaviors::INSERT],
                'options'    => ['foo' => 'bar'],
            ]);

        $this->createFixturesLoader()->load('behavior.yml');
    }

    public function testLoadResourcesWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/resource1.yml');
    }

    public function testLoadResourceWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/resource2.yml');
    }

    public function testLoadResourceConfigWithNonArray(): void
    {
        $this->expectException(ConfigurationException::class);

        $this->createFixturesLoader()->load('invalid/resource3.yml');
    }

    public function testLoadValidResource(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addBehavior');

        $this
            ->loader
            ->expects(self::once())
            ->method('addResource')
            ->with('acme.post', [
                'name'      => 'post',
                'namespace' => 'acme',
                'entity'    => [
                    'interface' => PostInterface::class,
                    'class'     => Post::class,
                ],
            ]);

        $this->createFixturesLoader()->load('resource.yml');
    }

    public function testLoadValidImport(): void
    {
        $this->loader->expects(self::never())->method('addPermission');
        $this->loader->expects(self::never())->method('addNamespace');
        $this->loader->expects(self::never())->method('addAction');
        $this->loader->expects(self::never())->method('addBehavior');

        $this
            ->loader
            ->expects(self::once())
            ->method('addResource')
            ->with('acme.comment', [
                'name'      => 'comment',
                'namespace' => 'acme',
                'entity'    => [
                    'class' => Comment::class,
                ],
            ]);

        $this->createFixturesLoader()->load('import.yml');
    }

    private function createFixturesLoader(): YamlFileLoader
    {
        return new YamlFileLoader($this->loader, new FileLocator([__DIR__ . '/../../Fixtures/config']));
    }
}

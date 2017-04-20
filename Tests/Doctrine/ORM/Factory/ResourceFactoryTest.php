<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM\Factory;

use Acme\Resource\Entity\Category;
use Acme\Resource\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourceFactoryTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceFactoryTest extends TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $managerRegistry;

    private ?ResourceFactory $resourceFactory;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->resourceFactory = new ResourceFactory();
        $this->resourceFactory->setManagerRegistry($this->managerRegistry);
        $this->resourceFactory->setClass(Post::class);
    }

    protected function tearDown(): void
    {
        $this->managerRegistry = null;
        $this->resourceFactory = null;
    }

    public function test_create()
    {
        self::assertInstanceOf(Post::class, $this->resourceFactory->create());
    }

    public function test_createFromContext_withWrongConfig()
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->method('getEntityClass')
            ->willReturn(Category::class);

        $context = $this->createMock(Context::class);
        $context
            ->method('getConfig')
            ->willReturn($config);

        $this->expectException(RuntimeException::class);

        $this->resourceFactory->createFromContext($context);
    }

    public function test_createFromContext_withoutParent()
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->method('getEntityClass')
            ->willReturn(Post::class);

        $context = $this->createMock(Context::class);
        $context
            ->method('getConfig')
            ->willReturn($config);

        self::assertInstanceOf(Post::class, $this->resourceFactory->createFromContext($context));
    }

    public function test_createFromContext_withParent()
    {
        // Category config
        $categoryConfig = $this->createMock(ResourceConfig::class);
        $categoryConfig->method('getEntityClass')->willReturn(Category::class);
        $categoryConfig->method('getCamelcaseName')->willReturn('category');
        $categoryConfig->method('getParentId')->willReturn(null);

        // Category context
        $categoryContext = $this->createMock(Context::class);
        $categoryContext->method('getConfig')->willReturn($categoryConfig);
        $categoryContext->method('getParent')->willReturn(null);
        $categoryContext->method('getResource')->willReturn($this->createMock(Category::class));

        // Post config
        $postConfig = $this->createMock(ResourceConfig::class);
        $postConfig->method('getEntityClass')->willReturn(Post::class);
        $postConfig->method('getParentId')->willReturn('acme.category');

        // Post context
        $postContext = $this->createMock(Context::class);
        $postContext->method('getConfig')->willReturn($postConfig);
        $postContext->method('getParent')->willReturn($categoryContext);
        $postContext->method('getResource')->willReturn(null);

        // Post metadata
        $metadata = $this->createMock(ClassMetadataInfo::class);
        $metadata
            ->method('getAssociationsByTargetClass')
            ->with(Category::class)
            ->willReturn([
                [
                    'type'      => ClassMetadataInfo::MANY_TO_ONE,
                    'fieldName' => 'category',
                ],
            ]);

        // Post (entity) manager
        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->with(Post::class)->willReturn($metadata);

        $this
            ->managerRegistry
            ->method('getManagerForClass')
            ->with(Post::class)
            ->willReturn($manager);

        /** @var Post $post */
        $post = $this->resourceFactory->createFromContext($postContext);

        self::assertInstanceOf(Post::class, $post);
        self::assertInstanceOf(Category::class, $post->getCategory());
    }

    public function test_createFromContext_withParentButNoMapping()
    {
        // Category config
        $categoryConfig = $this->createMock(ResourceConfig::class);
        $categoryConfig->method('getEntityClass')->willReturn(Category::class);
        $categoryConfig->method('getCamelcaseName')->willReturn('category');
        $categoryConfig->method('getParentId')->willReturn(null);

        // Category context
        $categoryContext = $this->createMock(Context::class);
        $categoryContext->method('getConfig')->willReturn($categoryConfig);
        $categoryContext->method('getParent')->willReturn(null);
        $categoryContext->method('getResource')->willReturn($this->createMock(Category::class));

        // Post config
        $postConfig = $this->createMock(ResourceConfig::class);
        $postConfig->method('getEntityClass')->willReturn(Post::class);
        $postConfig->method('getParentId')->willReturn('acme.category');

        // Post context
        $postContext = $this->createMock(Context::class);
        $postContext->method('getConfig')->willReturn($postConfig);
        $postContext->method('getParent')->willReturn($categoryContext);
        $postContext->method('getResource')->willReturn(null);

        // Post metadata
        $metadata = $this->createMock(ClassMetadataInfo::class);
        $metadata
            ->method('getAssociationsByTargetClass')
            ->with(Category::class)
            ->willReturn([]);

        // Post (entity) manager
        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getClassMetadata')->with(Post::class)->willReturn($metadata);

        $this
            ->managerRegistry
            ->method('getManagerForClass')
            ->with(Post::class)
            ->willReturn($manager);

        $this->expectException(RuntimeException::class);

        $this->resourceFactory->createFromContext($postContext);
    }

    public function test_createFromContext_withParentButNoResource()
    {
        // Category config
        $categoryConfig = $this->createMock(ResourceConfig::class);
        $categoryConfig->method('getEntityClass')->willReturn(Category::class);
        $categoryConfig->method('getCamelcaseName')->willReturn('category');
        $categoryConfig->method('getParentId')->willReturn(null);

        // Category context
        $categoryContext = $this->createMock(Context::class);
        $categoryContext->method('getConfig')->willReturn($categoryConfig);
        $categoryContext->method('getParent')->willReturn(null);
        $categoryContext->method('getResource')->willReturn(null);

        // Post config
        $postConfig = $this->createMock(ResourceConfig::class);
        $postConfig->method('getEntityClass')->willReturn(Post::class);
        $postConfig->method('getParentId')->willReturn('acme.category');

        // Post context
        $postContext = $this->createMock(Context::class);
        $postContext->method('getConfig')->willReturn($postConfig);
        $postContext->method('getParent')->willReturn($categoryContext);
        $postContext->method('getResource')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Parent resource is not available.");

        $this->resourceFactory->createFromContext($postContext);
    }

    public function test_createFromContext_withParentButNoContext()
    {
        // Post config
        $postConfig = $this->createMock(ResourceConfig::class);
        $postConfig->method('getEntityClass')->willReturn(Post::class);
        $postConfig->method('getParentId')->willReturn('acme.category');

        // Post context
        $postContext = $this->createMock(Context::class);
        $postContext->method('getConfig')->willReturn($postConfig);
        $postContext->method('getParent')->willReturn(null);
        $postContext->method('getResource')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Parent context is not available.");

        $this->resourceFactory->createFromContext($postContext);
    }
}

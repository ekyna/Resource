<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Registry;

use Acme\Resource\Entity\Baz;
use Acme\Resource\Entity\Category;
use Acme\Resource\Entity\Comment;
use Acme\Resource\Entity\Foo;
use Acme\Resource\Entity\Post;
use Acme\Resource\Entity\PostTranslation;
use Ekyna\Component\Resource\Config\Loader\ChildrenLoader;
use Ekyna\Component\Resource\Config\Registry\Cache;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistry;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourceRegistryTest
 * @package Ekyna\Component\Resource\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRegistryTest extends TestCase
{
    private const CATEGORY_DATA = [
        'namespace' => 'acme',
        'name'      => 'category',
        'entity'    => [
            'interface' => null,
            'class'     => Category::class,
        ],
        'event'     => [
            'priority' => 10,
        ],
    ];
    private const POST_DATA     = [
        'namespace'   => 'acme',
        'name'        => 'post',
        'entity'      => [
            'interface' => null,
            'class'     => Post::class,
        ],
        'event'       => [
            'priority' => 5,
        ],
        'translation' => [
            'interface' => null,
            'class'     => PostTranslation::class,
        ],
        'parent'      => 'acme.category',
    ];
    private const COMMENT_DATA  = [
        'namespace' => 'acme',
        'name'      => 'comment',
        'entity'    => [
            'interface' => null,
            'class'     => Comment::class,
        ],
        'event'     => [
            'priority' => 0,
        ],
        'parent'    => 'acme.post',
    ];

    private ?ResourceRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ResourceRegistry([], [
            'acme.category' => self::CATEGORY_DATA,
            'acme.post'     => self::POST_DATA,
            'acme.comment'  => self::COMMENT_DATA,
        ], [
            Category::class        => 'acme.category',
            Post::class            => 'acme.post',
            PostTranslation::class => 'acme.post',
            Comment::class         => 'acme.comment',
        ], ResourceConfig::class);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    private function config(string $name, array $data): ResourceConfig
    {
        $result = new ResourceConfig($name, $data);

        $result->setChildrenLoader(ChildrenLoader::create($this->registry));

        return $result;
    }

    public function testGet(): void
    {
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find('acme.post')
        );

        self::assertEquals(
            $this->config('acme.category', self::CATEGORY_DATA),
            $this->registry->find('acme.category')
        );

        $this->expectException(NotFoundConfigurationException::class);

        $this->registry->find('acme.baz');
    }

    public function testAll(): void
    {
        $expected = [
            'acme.category' => $this->config('acme.category', self::CATEGORY_DATA),
            'acme.post'     => $this->config('acme.post', self::POST_DATA),
            'acme.comment'  => $this->config('acme.comment', self::COMMENT_DATA),
        ];

        $actual = iterator_to_array($this->registry->all());

        self::assertEquals($expected, $actual);
    }

    public function testFindWithString(): void
    {
        // By id
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find('acme.post')
        );

        self::assertNull($this->registry->find('acme.baz', false));

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find('acme.baz');
    }

    public function testFindWithClass(): void
    {
        // By class
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find(Post::class)
        );

        self::assertNull($this->registry->find(Baz::class, false));

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find(Baz::class);
    }

    public function testFindWithObject(): void
    {
        // By object
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find(new Post())
        );

        self::assertNull($this->registry->find(new Baz(), false));

        $this->expectException(NotFoundConfigurationException::class);
        $this->registry->find(new Baz());
    }

    public function testFindById(): void
    {
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find('acme.post')
        );

        self::assertEquals(
            $this->config('acme.category', self::CATEGORY_DATA),
            $this->registry->find('acme.category')
        );

        self::assertNull($this->registry->find('acme.baz', false));

        $this->expectException(NotFoundConfigurationException::class);

        $this->registry->find('acme.baz');
    }

    public function testFindByIdCached(): void
    {
        $config = $this->config('acme.post', self::POST_DATA);

        $cache = $this->createMock(Cache::class);
        $cache->method('has')->with('acme.post')->willReturn(true);
        $cache->method('get')->with('acme.post')->willReturn($config);

        $cache->expects(self::once())->method('get');

        $this->registry->setCache($cache);

        $this->registry->find('acme.post');
    }

    public function testFindByClass(): void
    {
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->find(Post::class)
        );

        self::assertEquals(
            $this->config('acme.category', self::CATEGORY_DATA),
            $this->registry->find(Category::class)
        );

        self::assertNull($this->registry->find(Baz::class, false));

        $this->expectException(NotFoundConfigurationException::class);

        $this->registry->find(Baz::class);
    }

    public function testFindByTranslation(): void
    {
        // By class
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->findByTranslation(PostTranslation::class)
        );

        // By object
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->findByTranslation(new PostTranslation())
        );
        self::assertEquals(
            $this->config('acme.post', self::POST_DATA),
            $this->registry->findByTranslation((new PostTranslation())->setLocale('en')->setTranslatable(new Post()))
        );

        $this->expectException(LogicException::class);

        $this->registry->findByTranslation(Baz::class);
    }

    public function testGetParentMap(): void
    {
        $expected = [
            'acme.comment' => 'acme.post',
            'acme.post'    => 'acme.category',
        ];

        self::assertEquals($expected, $this->registry->getParentMap());
    }

    public function testGetEventPriorityMap(): void
    {
        $expected = [
            'acme.category' => 10,
            'acme.post'     => 5,
        ];

        self::assertEquals($expected, $this->registry->getEventPriorityMap());
    }
}

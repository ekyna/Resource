<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config;

use Acme\Resource\Entity\Category;
use Acme\Resource\Entity\Post;
use Acme\Resource\Entity\PostInterface;
use Acme\Resource\Entity\PostTranslation;
use Acme\Resource\Entity\PostTranslationInterface;
use Acme\Resource\Event\PostEvent;
use Acme\Resource\Factory\PostFactory;
use Acme\Resource\Factory\PostFactoryInterface;
use Acme\Resource\Manager\PostManager;
use Acme\Resource\Manager\PostManagerInterface;
use Acme\Resource\Repository\PostRepository;
use Acme\Resource\Repository\PostRepositoryInterface;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Event\ResourceEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourceConfigTest
 * @package Ekyna\Component\Resource\Tests\Config
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceConfigTest extends TestCase
{
    public function testBasic(): void
    {
        $config = $this->getBasicResourceConfig();

        self::assertEquals('acme.category', $config->getId());
        self::assertEquals('category', $config->getName());
        self::assertEquals('acme', $config->getNamespace());
        self::assertEquals('category', $config->getCamelCaseName()); // TODO
        self::assertNull($config->getParentId());

        self::assertNull($config->getEntityInterface());
        self::assertEquals(Category::class, $config->getEntityClass());

        self::assertNull($config->getTranslationInterface());
        self::assertNull($config->getTranslationClass());
        self::assertNull($config->getTranslationFields());

        self::assertEquals('acme.category.label.singular', $config->getResourceLabel());
        self::assertEquals('acme.category.label.plural', $config->getResourceLabel(true));

        self::assertEquals(ResourceEvent::class, $config->getEventClass());
        self::assertEquals(0, $config->getEventPriority());
        self::assertEquals('acme.category.test', $config->getEventName('test'));

        self::assertEquals([], $config->getActions());
        self::assertNull($config->getAction('test'));

        self::assertEquals([], $config->getBehaviors());
        self::assertNull($config->getBehavior('test'));

        self::assertEquals([], $config->getPermissions());
        self::assertFalse($config->hasPermission('test'));

        self::assertEquals('acme.category', $config->getTransPrefix());
        self::assertNull($config->getTransDomain());
    }

    public function testAdvanced(): void
    {
        $config = $this->getAdvancedResourceConfig();

        self::assertEquals('acme.post', $config->getId());
        self::assertEquals('post', $config->getName());
        self::assertEquals('acme', $config->getNamespace());
        self::assertEquals('post', $config->getCamelCaseName()); // TODO
        self::assertEquals('acme.category', $config->getParentId());

        self::assertEquals(PostInterface::class, $config->getEntityInterface());
        self::assertEquals(Post::class, $config->getEntityClass());

        self::assertEquals(PostTranslationInterface::class, $config->getTranslationInterface());
        self::assertEquals(PostTranslation::class, $config->getTranslationClass());
        self::assertEquals(['content'], $config->getTranslationFields());

        self::assertEquals('foo_bar.label.singular', $config->getResourceLabel());
        self::assertEquals('foo_bar.label.plural', $config->getResourceLabel(true));

        self::assertEquals(PostEvent::class, $config->getEventClass());
        self::assertEquals(10, $config->getEventPriority());
        self::assertEquals('acme.post.test', $config->getEventName('test'));

        self::assertEquals(['foo_action' => []], $config->getActions());
        self::assertEquals([], $config->getAction('foo_action'));

        self::assertEquals(['foo_behavior' => []], $config->getBehaviors());
        self::assertEquals([], $config->getBehavior('foo_behavior'));

        self::assertEquals(['foo'], $config->getPermissions());
        self::assertTrue($config->hasPermission('foo'));

        self::assertEquals('foo_bar', $config->getTransPrefix());
        self::assertEquals('Acme', $config->getTransDomain());
    }

    private function getBasicResourceConfig(): ResourceConfig
    {
        return new ResourceConfig('acme.category', [
            'namespace'    => 'acme',
            'name'         => 'category',
            'entity'       => [
                'interface' => null,
                'class'     => Category::class,
            ],
            'repository'   => [
                'interface' => null,
                'class'     => ResourceRepository::class,
            ],
            'manager'      => [
                'interface' => null,
                'class'     => ResourceManager::class,
            ],
            'factory'      => [
                'interface' => null,
                'class'     => ResourceFactory::class,
            ],
            'event'        => [
                'class'    => ResourceEvent::class,
                'priority' => 0,
            ],
            'translation'  => null,
            'parent'       => null,
            'actions'      => [],
            'behaviors'    => [],
            'permissions'  => [],
            'trans_prefix' => null,
            'trans_domain' => null,
        ]);
    }

    private function getAdvancedResourceConfig(): ResourceConfig
    {
        return new ResourceConfig('acme.post', [
            'namespace'    => 'acme',
            'name'         => 'post',
            'entity'       => [
                'class'     => Post::class,
                'interface' => PostInterface::class,
            ],
            'repository'   => [
                'interface' => PostRepositoryInterface::class,
                'class'     => PostRepository::class,
            ],
            'manager'      => [
                'interface' => PostManagerInterface::class,
                'class'     => PostManager::class,
            ],
            'factory'      => [
                'interface' => PostFactoryInterface::class,
                'class'     => PostFactory::class,
            ],
            'event'        => [
                'class'    => PostEvent::class,
                'priority' => 10,
            ],
            'translation'  => [
                'interface' => PostTranslationInterface::class,
                'class'     => PostTranslation::class,
                'fields'    => ['content'],
            ],
            'parent'       => 'acme.category',
            'actions'      => [
                'foo_action' => [],
            ],
            'behaviors'    => [
                'foo_behavior' => [],
            ],
            'permissions'  => [
                'foo',
            ],
            'trans_prefix' => 'foo_bar',
            'trans_domain' => 'Acme',
        ]);
    }
}

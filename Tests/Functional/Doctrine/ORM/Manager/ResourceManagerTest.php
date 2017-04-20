<?php

namespace Ekyna\Component\Resource\Tests\Functional\Doctrine\ORM\Manager;

use Acme\Resource\Entity\Category;
use Acme\Resource\Entity\Post;
use Ekyna\Component\Resource\Tests\app\Acme\Resource\DataFixtures\FixturesA;
use Ekyna\Component\Resource\Tests\Functional\TestCase;

/**
 * Class ResourceManagerTest
 * @package Ekyna\Component\Resource\Tests\Functional\Doctrine\ORM\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceManagerTest extends TestCase
{
    public function test_create(): void
    {
        /** @var \Ekyna\Component\Resource\Factory\ResourceFactoryInterface $factory */
        $factory = $this->get('acme.factory.category');

        /** @var Category $category */
        $category = $factory->create();
        $category->setTitle('Test category');

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.category');

        $event = $manager->create($category);
        $this->assertFalse($event->hasErrors());

        $category = $event->getResource();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotNull($id = $category->getId());

        $manager->clear();
    }

    public function test_create_translatable(): void
    {
        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.category');
        /** @var Category $category */
        $category = $repository->find(1);

        /** @var \Ekyna\Component\Resource\Factory\ResourceFactoryInterface $factory */
        $factory = $this->get('acme.factory.post');

        $date = (new \DateTime())->setTime(0, 0, 0, 0);

        /** @var Post $post */
        $post = $factory->create();
        $post
            ->setDate($date)
            ->setCategory($category)
            ->translate()
                ->setTitle('Super post title')
                ->setContent('Super post content');

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.post');

        $event = $manager->create($post);
        $this->assertFalse($event->hasErrors());

        $post = $event->getResource();
        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotNull($id = $post->getId());

        $manager->clear();

        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.post');

        $post = $repository->find($id);
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals($date, $post->getDate());

        $translation = $post->translate();
        $this->assertEquals('Super post title', $translation->getTitle());
        $this->assertEquals('Super post content', $translation->getContent());
    }

    public function test_update(): void
    {
        $this->clearDatabase();
        $this->load(FixturesA::class);

        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.category');
        /** @var Category $category */
        $category = $repository->find(1);

        $category->setTitle('New title');

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.category');

        $event = $manager->update($category);
        $category = $event->getResource();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertFalse($event->hasErrors());

        $manager->clear();

        $category = $repository->find(1);
        $this->assertEquals('New title', $category->getTitle());
    }

    public function test_update_translatable(): void
    {
        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.post');
        /** @var Post $post */
        $post = $repository->find(1);

        $date = clone $post->getDate();
        $date->modify('-1 day');
        $post
            ->setDate($date)
            ->translate()
                ->setTitle('New title');

        $post
            ->translate('fr', true)
                ->setTitle('Super titre')
                ->setContent('Super contenu');

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.post');

        $event = $manager->update($post);

        $post = $event->getResource();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertFalse($event->hasErrors());

        $manager->clear();

        $post = $repository->find(1);

        $this->assertEquals($date, $post->getDate());

        $translation = $post->translate('en');
        $this->assertEquals('New title', $translation->getTitle());

        $translation = $post->translate('fr');
        $this->assertEquals('Super titre', $translation->getTitle());
        $this->assertEquals('Super contenu', $translation->getContent());
    }

    public function test_delete()
    {
        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.category');
        /** @var Category $category */
        $category = $repository->find(1);

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.category');

        $event = $manager->delete($category);
        $this->assertInstanceOf(Category::class, $event->getResource());
        $this->assertFalse($event->hasErrors());

        $manager->clear();

        $this->assertNull($repository->find(1));
    }

    public function test_delete_translatable()
    {
        /** @var \Ekyna\Component\Resource\Repository\ResourceRepositoryInterface $repository */
        $repository = $this->get('acme.repository.post');
        /** @var Post $post */
        $post = $repository->find(1);

        /** @var \Ekyna\Component\Resource\Manager\ResourceManagerInterface $manager */
        $manager = $this->get('acme.manager.post');

        $event = $manager->delete($post);
        $this->assertInstanceOf(Post::class, $event->getResource());
        $this->assertFalse($event->hasErrors());

        $manager->clear();

        $this->assertNull($repository->find(1));
    }
}

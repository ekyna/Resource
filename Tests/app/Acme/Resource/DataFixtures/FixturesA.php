<?php

namespace Ekyna\Component\Resource\Tests\app\Acme\Resource\DataFixtures;

use Acme\Resource\Entity\Category;
use Acme\Resource\Entity\Comment;
use Acme\Resource\Entity\Post;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FixturesA
 * @package Ekyna\Component\Resource\Tests\app\Acme\Resource\DataFixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FixturesA implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        /** @var Category $category */
        $category = $this->container->get('acme.factory.category')->create();
        $category->setTitle('First category');

        $manager->persist($category);

         /** @var Post $post1 */
        $post1 = $this->container->get('acme.factory.post')->create();
        $post1
            ->setCategory($category)
            ->setDate(new \DateTime())
            ->translate()
                ->setTitle('First post title')
                ->setContent('First post content');

        $manager->persist($post1);

        /** @var Comment $comment1 */
        $comment1 = $this->container->get('acme.factory.comment')->create();
        $comment1
            ->setMessage('First comment')
            ->setPost($post1);

        $manager->persist($comment1);

        $manager->flush();
        $manager->clear();
    }
}

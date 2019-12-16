<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 100; $i++) {
            $post = new Post();
            $post->setTitle('post # ' . $i);
            $post->setText('post text');
            $manager->persist($post);
        }
        $manager->flush();
    }
}

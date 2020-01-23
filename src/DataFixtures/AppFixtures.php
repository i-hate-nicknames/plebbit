<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const NUM_POSTS = 100;

    private const NUM_USERS = 10;

    private const USER_PASSWORD_PREFIX = 'pass';

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::NUM_POSTS; $i++) {
            $post = new Post();
            $post->setTitle('post # ' . $i);
            $post->setText('post text');
            $manager->persist($post);
        }

        for ($i = 0; $i < self::NUM_USERS; $i++) {
            $user = new User();
            $user->setEmail("mail-$i@mail.com");
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                self::USER_PASSWORD_PREFIX . $i
            ));
            $manager->persist($user);
        }

        $manager->flush();
    }
}

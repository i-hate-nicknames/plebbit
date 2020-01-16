<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const NUM_POSTS = 100;

    private const NUM_USERS = 10;

    private const MAX_NUM_COMMENTS = 10;

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
        $general = new District();
        $general->setName('general');
        $general->setDescription('This is the general district');
        $manager->persist($general);

        $users = [];
        for ($i = 0; $i < self::NUM_USERS; $i++) {
            $user = new User();
            $user->setEmail("mail-$i@mail.com");
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                self::USER_PASSWORD_PREFIX . $i
            ));
            $user->setName('user-' . $i);
            $manager->persist($user);
            $users[] = $user;
        }

        for ($i = 0; $i < self::NUM_POSTS; $i++) {
            $post = new Post();
            $post->setTitle('post # ' . $i);
            $post->setText('post text');
            $post->setDistrict($general);
            $post->setAuthor($users[$i % self::NUM_USERS]);
            $manager->persist($post);
            // create n comments for each post, where n is (post_number % max_comments)
            for ($j = 0; $j < ($i % self::MAX_NUM_COMMENTS); $j++) {
                $comment = new Comment();
                $comment->setTitle("post$i-comment-$j");
                $comment->setText("Text $i $j");
                $comment->setPost($post);
                $comment->setAuthor($users[$j % self::NUM_USERS]);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function random_int;

class AppFixtures extends Fixture
{
    private const NUM_POSTS = 5;

    private const NUM_USERS = 10;

    private const MAX_NUM_COMMENTS = 10;

    private const USER_PASSWORD_PREFIX = 'pass';

    private const MAX_COMMENT_TREE_DEPTH = 2;

    private const MAX_COMMENT_CHILDREN = 10;

    private const DISTRICTS = [
        'general' => 'This is the general district containing generic and boring posts, welcome',
        'existentialcrisis' => 'Important questions as to why continue living are welcome, answers even more so',
        'news' => 'Happenigns around the world, big and not so much',
        'videogames' => 'Successfully escaping sad and boring reality',
        'shoplifting' => 'Shoplifters of the world, unite and take over',
        'pornography' => "L'important c'est d'aimer"
    ];

    private $commentCount = 0;

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

        $districts = [];
        $i = 0;
        foreach (self::DISTRICTS as $name => $desc) {
            $district = new District();
            $district->setName($name)
                ->setDescription($desc)
                ->setOwner($users[$i % self::NUM_USERS]);
            $manager->persist($district);
            $districts[] = $district;
            $i++;
        }

        for ($i = 0; $i < self::NUM_POSTS; $i++) {
            $post = new Post();
            $post->setTitle('post # ' . $i);
            $post->setText('post text');
            $post->setDistrict($districts[$i % count($districts)]);
            $post->setAuthor($users[$i % self::NUM_USERS]);
            $manager->persist($post);
            // only generate comment trees for the first three posts
            $commentDepth = ($i >= 3) ? 0 : self::MAX_COMMENT_TREE_DEPTH;
            // only 3 children for the nested trees
            $commentNumber = ($i >= 3) ? ($i % self::MAX_NUM_COMMENTS) : 3;
            // create n comments for each post, where n is (post_number % max_comments)
            for ($j = 0; $j < $commentNumber; $j++) {
                $this->generateCommentTree(
                    $commentDepth,
                    $commentNumber,
                    "p:$i;r:$j",
                    $manager,
                    $users[$j % self::NUM_USERS],
                    $post
                );
            }
        }

        $manager->flush();
    }

    // TODO: generate a couple of specific comment tree patterns: a very shallow but long tree, a very nested tree
    // both tall and nested (heated discussion)

    /**
     * Generate and persist a comment tree of given depth, each level having random number of children,
     * at most $numChildren
     * Return root comment
     * @param int $depth
     * @param int $numChildren
     * @param string $text
     * @param ObjectManager $manager
     * @param User $author
     * @param Post $post
     * @return Comment
     * @throws \Exception
     */
    private function generateCommentTree(
        int $depth,
        int $numChildren,
        string $text,
        ObjectManager $manager,
        User $author,
        Post $post
    ): Comment {
        $root = new Comment();
        $this->commentCount++;
        $printDepth = self::MAX_COMMENT_TREE_DEPTH - $depth;
        $rootText = "$text d:$printDepth #:$this->commentCount";
        $root->setTitle($rootText);
        $root->setText($rootText);
        $root->setAuthor($author);
        $root->setPost($post);
        if ($depth > 0) {
            for ($i = 0; $i < $numChildren; $i++) {
                $child = $this->generateCommentTree($depth-1, $numChildren, $text, $manager, $author, $post);
                $root->addChild($child);
            }
        }
        $manager->persist($root);
        return $root;
    }
}

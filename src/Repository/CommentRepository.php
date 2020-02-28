<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use ReflectionProperty;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function fetchTree(int $postId)
    {
        $roots = new ArrayCollection();
        $commentsFlat = $this->createQueryBuilder('c')
            ->select('c,u')
            ->andWhere('c.post = :post_id')
            ->setParameter('post_id', $postId)
            // todo: why left though? Comments always have authors, right?
            ->leftJoin('c.author', 'u')
            ->getQuery()
            ->getResult();

        // basically, what we do here is set "children" property of every
        // comment to be "initialized". This is doctrine way to tell
        // "you don't need to fetch it from db".
        // since we want to get all comments in a single query and build the tree ourselves,
        // we tell doctrine to avoid going to database when children property is accessed
        /** @var ReflectionProperty $prop */
        $prop = $this->getClassMetadata()->reflFields['children'];
        /** @var Comment $comment */
        foreach ($commentsFlat as $comment) {
            // instead of accessing through the getter (which immediately will force db fetch)
            // we access this field through reflection.
            // getValue will return a \Doctrine\ORM\PersistentCollection, which allows
            // to disable reloading
            $prop->getValue($comment)->setInitialized(true);
            if ($comment->getParent() === null) {
                $roots->add($comment);
            } else {
                $prop->getValue($comment->getParent())->setInitialized(true);
                $comment->getParent()->addChild($comment);
            }
        }
        return $roots;
    }
}

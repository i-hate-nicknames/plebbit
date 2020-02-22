<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getPostsListing(?User $user)
    {
        $userId = ($user) ? $user->getId() : 0;
        $sql = <<<SQL
            SELECT rated.id, rated.title, rated.rating, rated.current_vote, u.name,
                   rated.created_at, rated.updated_at, rated.author_id, u.name, u.email,
                   -- calculate the number of comments for every post
                   sum(CASE
                           WHEN c.id IS NULL THEN 0
                           ELSE 1
                       END)
                       AS comment_count
            FROM (
                     SELECT p.id, p.title, p.author_id, p.created_at, p.updated_at,
                            -- sum all the ratings, the posts that do not have a rating
                            -- will get 0 due to the following CASE
                            sum(CASE
                                    WHEN pv.value IS NULL THEN 0
                                    ELSE pv.value
                                END) AS rating,
                            -- calculate the voting status for current user
                            sum(CASE
                                    WHEN pv.user_id = ? THEN pv.value
                                    ELSE 0
                                END) AS current_vote
                     FROM post p
                              LEFT JOIN post_vote pv ON p.id = pv.post_id
                          -- filter only a single post
                          -- WHERE p.id = 1
                          -- filter by district
                          -- WHERE p.district_id = :id
                     GROUP BY p.id, p.title, p.author_id, p.created_at, p.updated_at
                 ) rated
                     LEFT JOIN `comment` c ON rated.id = c.post_id
                     JOIN user u on rated.author_id = u.id
            GROUP BY rated.id, rated.title, rated.rating, rated.current_vote, u.name,
                     rated.created_at, rated.updated_at, u.id, u.name, u.email
SQL;
        $rsm = new ResultSetMapping();
        // todo: add joined result of author?
        $rsm->addEntityResult(Post::class, 'p', 'post')
            ->addFieldResult('p', 'id', 'id')
            ->addFieldResult('p', 'title', 'title')
            ->addFieldResult('p', 'created_at', 'createdAt')
            ->addFieldResult('p', 'updated_at', 'updatedAt')
            ->addJoinedEntityResult(User::class, 'a', 'p', 'author')
            ->addFieldResult('a', 'author_id', 'id')
            ->addFieldResult('a', 'name', 'name')
            ->addFieldResult('a', 'email', 'email')
            ->addScalarResult('rating', 'rating', \Doctrine\DBAL\Types\Type::INTEGER)
            ->addScalarResult('comment_count', 'commentCount', \Doctrine\DBAL\Types\Type::INTEGER)
            ->addScalarResult('current_vote', 'currentVote', \Doctrine\DBAL\Types\Type::INTEGER);
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm)->setParameter(1, $userId);

        return $query->getResult();
    }
}

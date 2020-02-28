<?php

namespace App\Repository;

use App\Entity\District;
use App\Entity\Post;
use App\Entity\User;
use App\Factory\PostQueryBuilderFactory;
use App\Query\PostQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    /**
     * @var PostQueryBuilder
     */
    private $postQueryBuilder;
    /**
     * @var PostQueryBuilderFactory
     */
    private $qbFactory;

    public function __construct(ManagerRegistry $registry, PostQueryBuilderFactory $qbFactory)
    {
        parent::__construct($registry, Post::class);
        $this->qbFactory = $qbFactory;
    }

    public function getPostsListing(?User $user, $districts = null)
    {
        /** @var PostQueryBuilder $queryBuilder */
        $queryBuilder = $this->qbFactory->makePostQueryBuilder();
        $queryBuilder
            ->setCurrentUserId(($user) ? $user->getId() : 0);
        if ($districts != null) {
            $queryBuilder->setDistricts($districts);
        }
        $sql = $queryBuilder->build();
        $rsm = $this->getPostListResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }

    public function getSinglePost(?User $user, int $postId)
    {
        /** @var PostQueryBuilder $queryBuilder */
        $queryBuilder = $this->qbFactory->makePostQueryBuilder();
        $queryBuilder
            ->setPostId($postId)
            ->setCurrentUserId(($user) ? $user->getId() : 0);
        $sql = $queryBuilder->build();
        $rsm = $this->getPostListResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        try {
            return $query->getSingleResult();
        } catch (NoResultException $ex) {
            return [];
        }
    }

    /**
     * Get result set mapping for basic native query returned by PostQueryBuilder
     * If more fields are added to the query, they have to be added to result set mapping as well
     * @return ResultSetMapping
     */
    private function getPostListResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Post::class, 'p', 'post')
            ->addFieldResult('p', 'id', 'id')
            ->addFieldResult('p', 'title', 'title')

            ->addFieldResult('p', 'created_at', 'createdAt')
            ->addFieldResult('p', 'updated_at', 'updatedAt')
            ->addJoinedEntityResult(User::class, 'a', 'p', 'author')
            ->addFieldResult('a', 'author_id', 'id')
            ->addFieldResult('a', 'name', 'name')
            ->addFieldResult('a', 'email', 'email')
            ->addJoinedEntityResult(District::class, 'd', 'p', 'district')
            ->addFieldResult('d', 'district_id', 'id')
            ->addFieldResult('d', 'district_name', 'name')
            ->addScalarResult('rating', 'rating', \Doctrine\DBAL\Types\Type::INTEGER)
            ->addScalarResult('comment_count', 'commentCount', \Doctrine\DBAL\Types\Type::INTEGER)
            ->addScalarResult('current_vote', 'currentVote', \Doctrine\DBAL\Types\Type::INTEGER);
        return $rsm;
    }
}

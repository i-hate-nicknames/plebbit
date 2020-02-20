<?php

namespace App\Repository;

use App\Entity\PostVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PostVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostVote[]    findAll()
 * @method PostVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostVote::class);
    }

    // /**
    //  * @return PostVote[] Returns an array of PostVote objects
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
    public function findOneBySomeField($value): ?PostVote
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\District;
use App\Entity\Post;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method District|null find($id, $lockMode = null, $lockVersion = null)
 * @method District|null findOneBy(array $criteria, array $orderBy = null)
 * @method District[]    findAll()
 * @method District[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, District::class);
    }

    /**
     * @param string $name
     * @return District|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByName(string $name): ?District
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function updateNumSubscribers(int $id, int $diff)
    {
        $q = $this->createQueryBuilder('d')
            ->update()
            ->set('d.numSubscribed', 'd.numSubscribed + :diff')
            ->setParameter('diff', $diff)
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $q->execute();
    }
}

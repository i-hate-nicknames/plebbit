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

    public function findAllWithSubscribeStatus(?User $user)
    {
        $userId = ($user) ? $user->getId() : 0;
        $sql = <<<SQL
            SELECT d.id, d.name, d.description,
                   sum(CASE WHEN s.user_id = ? THEN 1 ELSE 0 END)
                   AS is_subscribed,
                   sum(CASE WHEN s.id IS NULL THEN 0 ELSE 1 END)
                   AS num_subscribers
            FROM district d
            LEFT JOIN subscription s on d.id = s.district_id
            GROUP BY d.id, d.name, d.description
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(District::class, 'd', 'district')
            ->addFieldResult('d', 'id', 'id')
            ->addFieldResult('d', 'name', 'name')
            ->addFieldResult('d', 'description', 'description')
            ->addScalarResult('is_subscribed', 'isSubscribed', \Doctrine\DBAL\Types\Type::INTEGER)
            ->addScalarResult('num_subscribers', 'numSubscribers', \Doctrine\DBAL\Types\Type::INTEGER);
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $userId);
        return $query->getResult();
    }
}

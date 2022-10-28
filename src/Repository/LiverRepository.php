<?php

namespace App\Repository;

use App\Entity\Liver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Liver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Liver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Liver[]    findAll()
 * @method Liver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LiverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Liver::class);
    }

    // /**
    //  * @return Liver[] Returns an array of Liver objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Liver
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

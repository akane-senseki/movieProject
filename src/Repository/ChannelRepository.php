<?php

namespace App\Repository;

use App\Entity\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Channel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Channel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Channel[]    findAll()
 * @method Channel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    private static $ALIAS_NAME = 'c';

    private function getQuerybuilder(){
        return $this->createQueryBuilder(self::$ALIAS_NAME);
    }

    public function getIdList()
    {
        $qb = $this->getQuerybuilder();

        $qb->select(self::$ALIAS_NAME.'.id');

        $result = $qb->getQuery()->getResult();

        $list = [];
        foreach($result as $r){
            $list[] = $r['id'];
        }

        return $list;
    }

    /*
    public function findOneBySomeField($value): ?Channel
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\ShippingItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShippingItem>
 *
 * @method ShippingItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingItem[]    findAll()
 * @method ShippingItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingItem::class);
    }

//    /**
//     * @return ShippingItem[] Returns an array of ShippingItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ShippingItem
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

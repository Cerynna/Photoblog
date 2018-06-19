<?php

namespace App\Repository;

use App\Entity\SousAlbum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SousAlbum|null find($id, $lockMode = null, $lockVersion = null)
 * @method SousAlbum|null findOneBy(array $criteria, array $orderBy = null)
 * @method SousAlbum[]    findAll()
 * @method SousAlbum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SousAlbumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SousAlbum::class);
    }

//    /**
//     * @return SousAlbum[] Returns an array of SousAlbum objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SousAlbum
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

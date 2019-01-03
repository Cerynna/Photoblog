<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function statPhotos()
    {



        $result = [];
        $result['total'] = $this->createQueryBuilder('p')
            ->select("count(p)")
            ->getQuery()
            ->getSingleScalarResult();
        $result['inAlbum'] = $this->createQueryBuilder('p')
            ->select("count(p)")
            ->where('p.album IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $result['inSousAlbum'] = $this->createQueryBuilder('p')
            ->select("count(p)")
            ->where('p.sousAlbum IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $result['inNoWhere'] = $this->createQueryBuilder('p')
            ->select("p")
            ->where('p.album IS NULL')
            ->andWhere('p.sousAlbum IS NULL')
            ->getQuery()
            ->getResult();

        $result['inFull'] = $this->createQueryBuilder('p')
            ->select("p")
            ->where('p.album IS NOT NULL')
            ->andWhere('p.sousAlbum IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $result;
    }


//    /**
//     * @return Photo[] Returns an array of Photo objects
//     */
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
    public function findOneBySomeField($value): ?Photo
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

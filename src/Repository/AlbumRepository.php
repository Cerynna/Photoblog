<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Album::class);
    }


    public function delete(Album $album)
    {
        $photos = $album->getPhotos()->toArray();
    }


    public function getCurrentPosition()
    {
        return $this->createQueryBuilder('a')
                ->select("count(a.id)")
                ->where('a.position IS NOT NULL')
                ->getQuery()
                ->getOneOrNullResult()[1] + 1;
    }


    public function albumIndex($maxResult = null)
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.status = 1')
            ->orderBy('a.position', 'DESC');
        if (!is_null($maxResult)) {
            $query->setMaxResults($maxResult);
        }


        return $query->getQuery()
            ->getResult();
    }

    public function adminAlbumIndex()
    {
        return [$this->createQueryBuilder('a')
            ->where('a.status = 1')
            ->orWhere('a.status = 2')
            ->orderBy('a.position', 'DESC')
            ->getQuery()
            ->getResult(),
            $this->createQueryBuilder('a')
                ->where('a.status = 0')
                ->getQuery()
                ->getResult(),
            $this->createQueryBuilder('a')
                ->where('a.status = 3')
                ->getQuery()
                ->getResult()
        ];
    }

    public function findInterval($position, $operator, $compFrist, $compLast)
    {
        return $this->createQueryBuilder('a')
            ->where('a.position ' . $compFrist . ' :first')
            ->andWhere('a.position ' . $compLast . ' :last')
            ->setParameter('first', $position)
            ->setParameter('last', $operator)
            ->getQuery()
            ->getResult();
    }

    public function getPrevAndNext($dir)
    {
        $album = $this->findOneBy(["dir" => $dir]);
        $pre = $this->createQueryBuilder('a')
            ->select("a.dir")
            ->andWhere('a.position = ' . ($album->getPosition() - 1))
            ->getQuery()
            ->getOneOrNullResult();
        $next = $this->createQueryBuilder('a')
            ->select("a.dir")
            ->andWhere('a.position = ' . ($album->getPosition() + 1))
            ->getQuery()
            ->getOneOrNullResult();

        return [
            "album" => $album,
            "prevAlbum" => (isset($pre)) ? $pre["dir"] : null,
            "nextAlbum" => (isset($next)) ? $next["dir"] : null
        ];

    }


//    /**
//     * @return Album[] Returns an array of Album objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Album
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\FormUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FormUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormUpload[]    findAll()
 * @method FormUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormUploadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FormUpload::class);
    }

//    /**
//     * @return FormUpload[] Returns an array of FormUpload objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FormUpload
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

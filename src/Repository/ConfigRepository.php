<?php

namespace App\Repository;

use App\Entity\Config;
use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Config::class);
    }

    /**
     * @return Photo[] Returns an array of Photo objects
     */
    public function getParallax()
    {
        $arrConfig = $this->createQueryBuilder('c')
            ->where('c.name LIKE :word ')
            ->setParameter('word', 'background%')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();


        $result = [];

        /** @var Config $config */
        foreach ($arrConfig as $config) {
            $result[] = $this->getEntityManager()->getRepository(Photo::class)->findOneBy(['id' => $config->getValue()]);
        }

        return $result;
    }

//    /**
//     * @return Config[] Returns an array of Config objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Config
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

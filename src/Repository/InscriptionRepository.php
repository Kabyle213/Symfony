<?php

namespace App\Repository;

use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Count;

/**
 * @extends ServiceEntityRepository<Inscription>
 *
 * @method Inscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inscription[]    findAll()
 * @method Inscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public function save(Inscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Inscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStatut($statut): ?array
    {
        return $this->createQueryBuilder('e')
        ->andwhere('e.statut = :statut')
        ->setParameter(':statut',$statut)
        ->getQuery()
        ->getResult()
        ;
    }
    public function findByFormationEtEmploye($idFormation, $idEmploye): ?array
    {
        return $this->createQueryBuilder('e')
        ->andwhere('e.formation= :idFormation')
        ->andwhere('e.employe = :idEmploye')
        ->setParameter(':idEmploye', $idEmploye)
        ->setParameter(':idFormation',$idFormation)
        ->getQuery()
        ->getResult()
        ;
    }
    public function findByFormationCount(): ?array{
        return $this->createQueryBuilder('e')
        ->select("Count(e.formation), e.formation")
        ->setMaxResults(3)
        ->getQuery()
        ->getResult()
        ;
    }
    public function findByIdEmploye($id): ?array{
        return $this->createQueryBuilder('e')
            ->andWhere('e.employe = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByIdEmployeNotInscrit($idEmploye): ?array{
        return $this->createQueryBuilder('e')
            ->andWhere("e.employe != :id")
            ->setParameter('id', $idEmploye)
            ->getQuery()
            ->getResult()
        ;
    }
  
//    /**
//     * @return Inscription[] Returns an array of Inscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Inscription
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

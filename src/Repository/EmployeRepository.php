<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 *
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    public function save(Employe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByLoginMdp($login, $mdp): ?Employe{
        return $this->createQueryBuilder('e')
            ->andWhere('e.login= :login')
            ->andWhere('e.mdp= :mdp')
            ->setParameter(':login',$login)
            ->setParameter(':mdp', $mdp)
            ->getQuery()
            ->getOneOrNullResult()    
        ;
    }
    public function findByLogin($login):?Employe{
        return $this->createQueryBuilder('e')
            ->andWhere('e.login= :login')
            ->setParameter(':login', $login)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findByStatut($statut): ?array{
        return $this->createQueryBuilder('e')
            ->andWhere('e.statut= :statut')
            ->setParameter(':statut', $statut)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Employe[] Returns an array of Employe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Employe
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

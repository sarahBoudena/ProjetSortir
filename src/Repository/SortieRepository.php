<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSortieWithLieu():array {
        $reqSQL ="
            SELECT s FROM APP\Entity\Sortie s
            INNER JOIN \APP\Entity\Lieu l
            ON s.lieu_id = l.id
        ";
        $req = $this->getEntityManager()->createQuery($reqSQL);
        return $req->getResult();
    }




//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
    public function findByFilter($site, $nom, $dateDebut, $dateFin, $organisateur, $inscrit, $nonInscrit, $passe): array
    {
        $db = $this->createQueryBuilder('s');

            if($organisateur!=null OR $inscrit!=null OR $nonInscrit!=null){
                $db->join('s.participants', 'p' );
            }

            if($organisateur!=null){
                $db->andWhere('s.organisateur = orga' );
                $db->setParameter('orga', $organisateur);
            }

            if ($inscrit){
                $db->andWhere('p.inscription = user' );
                $db->setParameter('user', $inscrit);
            }

            if ($nonInscrit){
                $db->andWhere('p.inscription IS NOT user' );
                $db->setParameter('user', $nonInscrit);
            }

            if ($passe){

            }

            if ($passe){

            }

            $db->andWhere('s.site = site');
            $db->setParameter('site', $site);


//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    // on ajoute une fonction pour la pagination
    public function findProductsPaginated(int $page, ?string $slug, int $limit = 6): array
    {
        // on met un absolu à la limite pour être sûr qu'elle soit toujours positive
        $limit = abs($limit);

        // on initialise un tableau pour mettre les résultats dedans
        $result = [];

        // requête pour aller chercher les catégories et le produits en filtrant sur le slug
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('c', 'p')
            ->from('App\Entity\Products', 'p')
            ->join('p.categories', 'c');
            
        // Si le slug est spécifié, ajoutez une condition de filtrage
        if ($slug !== null) {
            $query->where("c.slug = '$slug'");
        }

        $query->setMaxResults($limit)

            // on détermine le nombre d'article par page en changeant de page au bout d'un certain nombre de pages
            ->setFirstResult(($page * $limit) - $limit);

        // on pagine
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        // on vérifie qu'on a des données
        if (empty($data)) {
            return $result;
        }

        // on calcule le nombre de pages
        $pages = ceil($paginator->count() / $limit);

        // on remplit le tableau
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;

        return $result;
    }

    public function save(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Products[] Returns an array of Products objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Products
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
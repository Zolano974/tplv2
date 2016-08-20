<?php

namespace FirstBundle\Repository;

use Doctrine\ORM\EntityRepository;

use FirstBundle\Entity\Workset;

/**
 * WorksetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WorksetRepository extends EntityRepository
{
    
    public function fetchOneWithFields($id){

        $qb = $this ->createQueryBuilder('w')
                    ->leftJoin('w.fields', 'f')
                    ->where('w.id = ' . $id)
                    ->addSelect('f');
        
        return $qb->getQuery()->getResult()[0];
     
    }
    
    public function fetchAllWithFields(){

        $qb = $this ->createQueryBuilder('w')
                    ->leftJoin('w.fields', 'f')
                    ->addSelect('f');
        
        return $qb->getQuery()->getResult();
     
    }
}


//        
//    $qb = $this->createQueryBuilder('a');
//
//    // On fait une jointure avec l'entité Categorie, avec pour alias « c »
//    $qb ->join('a.categories', 'c')
//        ->where($qb->expr()->in('c.nom', $nom_categories)); // Puis on filtre sur le nom des catégories à l'aide d'un IN
//
//    // Enfin, on retourne le résultat
//    return $qb->getQuery()
//              ->getResult();   

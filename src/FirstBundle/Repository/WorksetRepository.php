<?php

namespace FirstBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * WorksetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WorksetRepository extends EntityRepository
{
    
    public function fetchOnWithFields(){

//        $qb = $this ->createQueryBuilder('w')
//                    ->leftJoin('w.fields', 'f')
//                    ->addSelect('f');
//        
//        return $qb->getQuery()->getResult();
    }
}

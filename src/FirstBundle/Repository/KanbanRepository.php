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
class KanbanRepository extends EntityRepository
{
    
    //on récupère la liste des items, pour populer le kanban de cette matière pour cette itération (avec les num de step)
    public function fetchAllItemsByFieldAndIteration($field_id, $iteration, $user_id){
        
        
        $qb = $this ->getEntityManager()
                    ->getConnection()
                    ->createQueryBuilder();
        
//        $query = $qb->select('t.item_id, i.name as item_name, t.iteration, t.user_id, k.step, ti.done, t.field_id')
//                    ->from('tourXitem', 't')
////                    ->where('t.field_id = :f_id')
////                    ->andWhere('t.iteration = :it')
////                    ->andWhere(' t.user_id = :u_id')
//                    ->leftJoin('t','kanban_item_step', 'k', 'k.item_id = t.item_id AND k.user_id = t.user_id AND k.iteration = t.iteration')
//                    ->leftJoin('t','item', 'i', 't.item_id = i.id')
//                    ->leftJoin('t','link_tour_item', 'ti', 't.tour_id = ti.tour_id AND t.user_id = ti.user_id AND t.item_id = ti.item_id')
//                    ->setParameter('f_id', $field_id)
//                    ->setParameter('it', $iteration)
//                    ->setParameter('u_id', $user_id)
//                    ;
        
        $query =    $qb ->select('item_id, item_name, iteration, user_id, step, done')
                        ->from('kanbanXitem')
                        ->where('field_id = :f_id')
                        ->andWhere('iteration = :it')
                        ->andWhere(' user_id = :u_id')    
                        ->setParameter('f_id', $field_id)
                        ->setParameter('it', $iteration)
                        ->setParameter('u_id', $user_id);                
                        
        
//        dump($query->getSQL());die;
        
        $result = $qb   ->execute()
                        ->fetchAll();
        
        $output = [];
        
        foreach($result as $row){
            
            if($row['done'] == '1') $row['step'] = 2;
            
            $output[] = $row;
        }
        
        return $output;
        
    }
 
   
    public function stepUp($item_id, $iteration, $user_id){
        
        $item_kanban = $this->findBy(array(
                                            'item_id' => $item_id,
                                            'iteration' => $iteration,
                                            'user_id' => $user_id,
        ));
        
        $item = $item_kanban[0];

        //le step 3 est le dernier
        if($item->getStep() < 2){
            $item->setStep($item->getStep() + 1);
        }
        
        return $item;
        
    }
}


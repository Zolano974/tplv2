<?php

namespace FirstBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Zolano\FluxinBundle\Repository\InfluxRepository;
/**
 * ItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ItemRepository extends EntityRepository
{
    public function fetchAllByFieldId($id){
        return $this->findBy(array('field' => $id));
    }
    
    public function mikbook($item_id, $user_id){
        
        $cnx = $this->getEntityManager()->getConnection();
        
        $cnx->insert('item_mikbook', array(
                    'item_id'   => $item_id,
                    'user_id'  => $user_id,
                ));
        
        return true;
    }

    //renvoie un booléen décrivant si l'item a été mikbooké par l'utilisateur en param
    public function isMikBooked($id, $user_id){
        
        $qb =   $this   ->getEntityManager()
                        ->getConnection()
                        ->createQueryBuilder();
        
        $query  = $qb   ->select('count(id) as count')
                        ->from('item_mikbook')
                        ->where('item_id = :i')
                        ->andWhere('user_id = :u')
                        ->setParameter('i',$id)
                        ->setParameter('u',$user_id);
        
        $result = $query->execute()->fetch();
        
        return $result['count'] > 0;
    }
    
   //met à jour en BD le fait que cet item soit done par l'user, pour l'iteration en paramètre
    //la valeur de retour est un bololéen qui indique, après modif, si la matière a été terminée pour cette itération après l'ajout de l'item
    public function done($item_id, $iteration, $user_id){
        
        $qb =   $this   ->getEntityManager()
                        ->getConnection()
                        ->createQueryBuilder();
        
        //on récupère l'ID du tour pour l'itération et l'utilisateur en paramètre
        $query = $qb->select('id')
                    ->from('tour')
                    ->where('iteration = :i')
                    ->andWhere('user_id = :u')
                    ->setParameter('i', $iteration)
                    ->setParameter('u', $user_id);
        
        $result = $query    ->execute()
                            ->fetch();
        
        $tour_id = $result['id'];
        
        //on set la valeur de done à 1 pôur cet utilisateur et ce tour
        $query_upd = $qb->update('link_tour_item')
                        ->set('done', 1)
                        ->where('tour_id = :t')
                        ->andWhere('item_id = :i')
                        ->andWhere('user_id = :u')
                        ->setParameter('t', $tour_id)
                        ->setParameter('i', $item_id)
                        ->setParameter('u', $user_id);
        
        $update = $query_upd->execute();
        
        $field_id =  $this  ->find($item_id)
                            ->getField()
                            ->getId();  
        
        //booléen : matière complète
        $field_done = $this->allFieldItemsDone($item_id, $user_id, $iteration);
        
        //si la matière est complète
        if($field_done){
//         //on inssère cette info en DB
           $this->setItemFieldDone($field_id, $user_id, $tour_id);
        }
        
        return array(
            'field_id'      => $field_id,
            'field_done'    => $field_done,
        );
                
    }
    
    //renvoie un booléen décrivant si la matière compoertant l'item en paramètre à été terminée par l'utilisateur en param, pour l'iteration en param
    public function allFieldItemsDone($item_id, $user_id, $iteration){
        
        $items_same_field = $this   ->find($item_id)
                                    ->getField()
                                    ->getItems();

        //on fix le booléen a TRUE
        $isFieldComplete = true;
        
        //si on trouve un seul item non terminé, on set à FALSE
        foreach($items_same_field as $i){
            if(!($this->isDone($i->getId(), $user_id)[$iteration])){
                $isFieldComplete = false;
            }
        }
        
        return $isFieldComplete;
    }
    
    //set à done le field comportant l'item en param, pour l'user et le tour passés en param
    private function setItemFieldDone($field_id, $user_id, $tour_id){
        
        $qb =   $this   ->getEntityManager()
                        ->getConnection()
                        ->createQueryBuilder();     
        
        //on set la valeur de done à 1 pôur cet utilisateur et ce tour
        $query_upd = $qb->update('link_tour_field')
                        ->set('done', 1)
                        ->where('tour_id = :t')
                        ->andWhere('field_id = :f')
                        ->andWhere('user_id = :u')
                        ->setParameter('t', $tour_id)
                        ->setParameter('f', $field_id)
                        ->setParameter('u', $user_id);

        $update = $query_upd->execute();        
        
    }
      
    //renvoie un tableau comportant l'état( done ou non) de l'item en paramètre pour chaque itération présente en base
    public function isDone($id, $user_id){
        
        $outputData = array();
        
        $qb =   $this   ->getEntityManager()
                        ->getConnection()
                        ->createQueryBuilder();
        
        $query  = $qb   ->select('iteration, done')
                        ->from('view_link_user_item')
                        ->where('item_id = :i')
                        ->andWhere('user_id = :u')
                        ->setParameter('i',$id)
                        ->setParameter('u',$user_id);
        
        $result = $query->execute()->fetchAll();
        
        foreach($result as $row){
            $outputData[$row['iteration']] = ($row['done'] == 1);
            
        }
        
        return $outputData;
    }
    
    ############# FONCTIONS LIEES A INFLUXDB ###########"""
    
        //
    /**
     * Renvoie l'agrégation des items cochés, granularité en paramètre
     *
     * @param FieldId           $field_id       The ID of the field wanted. -1 means global
     * @param Begin             $begin          The beginning of the period wanted. Expected format : timestamp ( on Z ?)
     * @param End               $end            The end of the period wanted. Expected format : timestamp ( on Z ?)
     * @param Aggreg            $aggreg         The granularity wanted : ENUM ( (hour, day, week, month)
     */        
    public function getItemsDoneAggregate($begin, $end, $field_id = -1, $aggreg = 'hour'){
        
        $agregation = "";
        
        switch($aggreg){
            case 'hour' :
    
                $agregation = '1h';
                break;
            case 'day' :
         
                $agregation = '1d';
                break;
            case 'week' :
  
                $agregation = '1w';
                break;
            case 'month' :
  
                $agregation = '4w';
                break;

        }

        
        //on crée la condition sur matiere uniquement si différent de -1
        $where_condition = ($field_id == -1) ? "" : "field_id = '$field_id' GROUP BY time($agregation)";
        
        $influx = $this->getInfluxRepository();
        
        $database = "test";
        
        $data = $influx->selectMetrics("count(done)", "items_done", $begin, $end, $where_condition, $database);
        
        return $data;
        
    }
    
     //
    /**
     * Renvoie la data des items cochés/mikbookés
     *
     * @param UserID            $user_id        The ID of the field wanted. -1 means global
     * @param FieldId           $field_id       The ID of the field wanted. -1 means global
     * @param Begin             $begin          The beginning of the period wanted. Expected format : timestamp ( on Z ?)
     * @param End               $end            The end of the period wanted. Expected format : timestamp ( on Z ?)
     * @param Mikbook           $mikbook        true : items mikbookés /  false : items cochés
     */       
    public function fetchItemsInfluxData($user_id, $begin = null, $end = null, $mikbook = false, $field_id = null, $aggreg = 'day'){
        

            $series = array();

            //on assure les valeurs de BEGIN et END
            $date = date("Y-m-d");
    //        $last_week = date("Y-m-d",mktime(0,0,0,date("m"), date("d")-7, date("Y")));
            $last_month = date("Y-m-d",mktime(0,0,0,date("m")-1, date("d"), date("Y")));

            if($begin === null){ $begin = $last_month; }
            if($end === null){ $end = $date; }        
            
            $data = ($mikbook) ? $this->getItemsMkbAggregate($begin, $end, $user_id, $field_id, $aggreg) : $this->getItemsDoneAggregate($begin, $end, $user_id, $field_id, $aggreg) ;
            
            return $data;
    }
    
    //
    /**
     * Renvoie l'agrégation des items miknookés, granularité en paramètre
     *
     * @param FieldId           $field_id       The ID of the field wanted. -1 means global
     * @param Begin             $begin          The beginning of the period wanted. Expected format : timestamp ( on Z ?)
     * @param End               $end            The end of the period wanted. Expected format : timestamp ( on Z ?)
     * @param Aggreg            $aggreg         The granularity wanted : ENUM ( (hour, day, week, month)
     */        
    public function getItemsMkbAggregate($begin, $end, $user_id, $field_id = -1, $aggreg = 'hour'){
        
        $agregation = "";
        
        switch($aggreg){
            case 'hour' :
    
                $agregation = '1h';
                break;
            case 'day' :
         
                $agregation = '1d';
                break;
            case 'week' :
  
                $agregation = '1w';
                break;
            case 'month' :
  
                $agregation = '4w';
                break;

        }
        
        $begin = "'$begin'";
        if($end != 'now()') $end = "'$end' GROUP BY time($agregation)";

        
        //on crée la condition sur matiere uniquement si différent de -1
        $where_condition = ($field_id == -1) ? "" : " AND field_id = '$field_id'";
        
        $influx = $this->getInfluxRepository();
        
        $database = "test";
        
        $data = $influx->selectMetrics("count(done)","items_mkb", $begin, $end, $where_condition, $database);
        
        return $data;
        
    }    
    
    //
    /**
     * Ecrit un point dans influxDB 
     *
     * @param ItemId            $item_id        The ID of the item to be written
     * @param UserId            $user_id        The bID of the user to be written
     * @param FieldId           $field_id       The bID of the field to be written
     * @param Mkb               $mkb            Wether the item has benn mikbooked TRUE or done FALSE
     */      
    public function markInfluxDBItem($item_id, $user_id, $field_id, $mkb = false){

        $mark_array = [
            "tags" => [
                "item_id" => "$item_id" ,
                "field_id" => "$field_id",
                "user_id" => "$user_id",
            ],
            "points" => [
                [
                    "measurement" => ($mkb) ? "items_mkb" : "items_done",
                    "fields"    => [
                        "done" => 1,
                    ]
                ],
            ],
        ];    
        
        $influx = $this->getInfluxRepository();    

        return $influx->mark($mark_array);
    }        
        
    
    
    private function getInfluxRepository(){
        
       return  new InfluxRepository($this->getEntityManager());
        
         
    }
    
}

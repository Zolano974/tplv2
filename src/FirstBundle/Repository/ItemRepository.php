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
    ############# FONCTIONS LIEES A INFLUXDB ###########"""
    ############# FONCTIONS LIEES A INFLUXDB ###########"""
    ############# FONCTIONS LIEES A INFLUXDB ###########"""
    
    //renvoie toutes les courbes des matières du workset, ainsi que la courbe agrégée
    public function loadWorksetData($user_id, $workset, $begin = null, $end = null, $mikbook = false, $aggreg = 'day'){
        
        $fields = $workset->getFields();
        $fields[] = null;
        
        return $this->loadFieldsData($user_id, $workset->getId(), $workset->getFields(), $begin, $end, $mikbook, $aggreg);
    }
    
    

    /**     fonction dédiée à récupérer les données et les paramètres AMCHARTS pour un workset                   FONCTION DU PLUS HAUT NIVEAU D'ORCHESTRATION !!!
     * @param $graph            GraphEntity correspondant au workset (ou autre), muni d'une liste de Fields, et d'une fonction getFieldList()
     * @param $field_id         GraphEntity correspondant au workset (ou autre), muni d'une liste de Fields, et d'une fonction getFieldList()
     * @param null $begin       date de début de la récupération au format "Y-m-d",;
     * @param null $end         date de fin de la récupération au format "Y-m-d"
     * @param bool $mikbook     items DONE ou MIKBOOKED
     * @return array, composé de 4 champs :
     *                                      'graph'         =>  L'entité Graph passé en entrée
     *                                      'kpi_data'      =>  Les séries collectées, remaniées mais pas formatées pour AmCharts                                   (construction d'un tableau de données dans la vue)
     *                                      'chart_params'  =>  Les paramètres du graphe (en JSON), directement pluggable dans ungraphe amCharts :                  chart = AmCharts.makeChart("html_id", chart_params);
     *                                      'chart_data'    =>  Les données formatées pour Amcharts (en JSON), directement pluggable dans un graphe amChart :       chart.dataProvider = generateChartData(chart_data);
     */
    public function loadFieldsData($user_id, $workset_id, $fields = array(), $begin = null, $end = null, $mikbook = false, $aggreg = 'day'){
        
        $influx = $this->getInfluxRepository();
//
        //on récupère les données des KPI du graphe
        $series = $this->fetchFieldsData($user_id, $workset_id, $fields, $begin, $end, $mikbook, $aggreg);

        //on formatte le résultat dans un format facilement exploitable par la suite
        $series =  $influx->formatSeries($series);        

        //on formate les données + convert JSON pour les rendre exploitables par les graphes AmCharts
        $chart_data = $influx->formatData4AmCharts($series);

        //on obtient un objet JSON représentant les paramètres du graphe AmCharts, appliquables directement
        $chart_params = $influx->getAmChartsJsonParams($series);

        //on ordonne le résultat dans un structure à 4 champs
        $output = array(
            'series'        => $series,
            'chart_data'    => $chart_data,
            'chart_params'  => $chart_params,
        );

        return $output;

    }    
    

    /**         Renvoie les données pour l'utilisateur et tous lesFields passés en oaram
     * 
     * @param type $user_id
     * @param type $workset_id
     * @param type $fields                  //un tableau avec les fields a requêter. Un élément NULL signifie l'agrégation de tous ceux du workset
     * @param type $begin
     * @param type $end
     * @param type $mikbooked
     * @param type $aggreg
     * @return type
     */
    public function fetchFieldsData($user_id, $workset_id, $fields = array(), $begin = null, $end = null , $mikbooked = false, $aggreg = 'day'){

        $series = array();

        //on assure les valeurs de BEGIN et END
        $date = date("Y-m-d");
        //$last_week = date("Y-m-d",mktime(0,0,0,date("m"), date("d")-7, date("Y")));
        $last_month = date("Y-m-d",mktime(0,0,0,date("m")-1, date("d"), date("Y")));

        //si les dates ne sont pas renseignées, on met les bornes par défaut (de ya un mois à AJD)
        if($begin === null){ $begin = $last_month; }
        if($end === null){ $end = $date; }

        //pour chaque indicateur du graphe
        foreach($fields as $field){
            //si lefield est null alors on requête leworkset agrégé
            $field_id = ($field !== null) ? $field->getId() : null;
            $field_name = ($field !== null) ? $field->getName() : "Total";
            
            $data =  $this->getItemsAggregate($begin, $end, $user_id, $workset_id, $field_id, $aggreg, $mikbooked);

            if(count($data) > 0) $series[$field_name] = $data;
        }
        
//
//        //on formatte le résultat dans un format facilement exploitable par la suite
//        return $this->formatSeries($series);
        return $series;

    }        
    
    
    
    //
    /**
     * Renvoie l'agrégation des items miknookés, granularité en paramètre
     *
     * @param UserId            $user_id        The ID of the user to be queried
     * @param Worksetd          $workset_id     The ID of the workset wanted.
     * @param FieldId           $field_id       The ID of the field wanted. NULL means all this workset's fields agregated
     * @param Begin             $begin          The beginning of the period wanted. Expected format : YYYY-MM-DD
     * @param End               $end            The end of the period wanted. Expected format : YYYY-MM-DD
     * @param Aggreg            $aggreg         The granularity wanted : ENUM ( (hour, day, week, month)
     * @param Mkb               $mkb            Either we fetch DONE or MIKBOOKED items
     */        
    public function getItemsAggregate($begin, $end, $user_id, $workset_id, $field_id = null, $aggreg = 'day', $mkb = false){
        
        $agregation = "day";
        
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
        
        $collection = ($mkb) ? "item_mkb" : "items_done" ;
        
        //on crée la condition sur matiere uniquement si différent de -1
        $where_condition = " workset_id = '$workset_id'";
        
        //on crée la condition sur matiere uniquement si différent de -1
        $where_condition .= ($field_id == null) ? "" : " AND field_id = '$field_id'";
        
        $groupby =  " GROUP BY time($agregation)";
        
        $influx = $this->getInfluxRepository();
        
        $brute_data = $influx->selectMetrics("count(done)", $collection, $begin, $end, $where_condition, $groupby, null);
        
        $data = $influx->Influx2Array($brute_data);
        
        return $data;
        
    }    
    

    
    //
    /**
     * Ecrit un point dans influxDB 
     *
     * @param ItemId            $item_id        The ID of the item to be written
     * @param UserId            $user_id        The bID of the user to be written
     * @param WorksetId         $workset_id     The bID of the user to be written
     * @param FieldId           $field_id       The bID of the field to be written
     * @param Mkb               $mkb            Wether the item has benn mikbooked TRUE or done FALSE
     */      
    public function markInfluxDBItem($item_id, $user_id, $workset_id, $field_id, $mkb = false){

        $mark_array = [
            "tags" => [
                "item_id"       => "$item_id",
                "field_id"      => "$field_id",
                "workset_id"    => "$workset_id",
                "user_id"       => "$user_id",
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
        
    
    /** Instancie un Influxrepository
     * 
     * @return InfluxRepository
     */
    private function getInfluxRepository(){
        
       return  new InfluxRepository($this->getEntityManager());
        
         
    }
    
}

<?php

namespace FirstBundle\Helpers\InfluxDB;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use FirstBundle\Helpers\InfluxDB\InfluxDBAdapter;


class InfluxRepository{

    private $influxAdapter;
    
    private $em;
    
    /**
     * Initializes a new <tt>InfluxRepository</tt>.
     *
     * @param EntityManager         $em    The EntityManager to use.
     */    
    public function __construct($em){
        
        $this->em = $em;
        $this->influxAdapter = new InfluxDBAdapter();
    }
    
    
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
        
         //on retire le décalage avec GMT aux bornes temporelles pour bien avoir les données de minuit à minuit
        $begin = $this->addGMToffset($begin . " 00:00:00", true);
        $end = $this->addGMToffset($end . " 00:00:00", true);        

        
        //on crée la condition sur matiere uniquement si différent de -1
        $field_condition = ($field_id == -1) ? "" : " AND field_id = '$field_id'";
        
        $query = "SELECT count(done) FROM items_done WHERE time > '$begin' AND time < '$end' $field_condition GROUP BY time($agregation)";
        
        return $this->influxAdapter->query($query);
        
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
    public function fetchItemsDoneInfluxData($user_id, $begin = null, $end = null, $mikbook = false, $field_id = null, $aggreg = 'day'){
        

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
        if($end != 'now()') $end = "'$end'";

        
        //on crée la condition sur matiere uniquement si différent de -1
        $field_condition = ($field_id == -1) ? "" : " AND field_id = '$field_id'";
        
        $query = "SELECT count(done) FROM items_mkb WHERE time > $begin AND time < $end AND user_id = '$user_id' $field_condition GROUP BY time($agregation)";
        
        return $this->influxAdapter->query($query);
        
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
        
        return $this->influxAdapter->mark($mark_array);
    }    
    
    
    
       
        //ajoute les heures d'écart avec GMT.
        //      l'attribut remove définit si l'écart doit etre soustrait ou ajouté (pour les bornes temporelles, on doit soustraire, pour els données rajouter)
        private function addGMToffset($date, $remove = false){
           
                    //on récupère le décalage GMT
                    $decalage = date('P');
                    $dec_hm = explode('+',$decalage)[1];
                    $dec_h =  substr($dec_hm, 0, -3);
                    $int_dec_h = intval($dec_h);
                    //on ,passe en timestamp
                    $timestamp = strtotime($date);
                    //on ajoute l'offset au timestamp
                    $timestamp = (!$remove) ? $timestamp + $int_dec_h * 60 * 60 : $timestamp - $int_dec_h * 60 * 60 ;
                    //on formatte la date au format d'output
                    $date_offset = date('Y-m-d H:i:s', $timestamp);
                   
                    return $date_offset;
        }
            
}
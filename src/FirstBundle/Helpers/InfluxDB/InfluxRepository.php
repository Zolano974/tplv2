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
    public function getItemsDoneAggregate($begin, $end, $field_id = -1, $aggreg = 'day'){
        
        $agregation = "";
        
        switch($aggreg){
            case 'day' :
                $agregation = '1d';
            case 'week' :
                $agregation = '1w';
            case 'month' :
                $agregation = '4w';

        }
        
        //on crée la condition sur matiere uniquement si différent de -1
        $field_condition = ($field_id == -1) ? "" : " AND field_id = '$field_id'";
        
        $query = "SELECT count(done) FROM items_done WHERE time > $begin AND time < $end $field_condition GROUP BY time($agregation)";
        
        dump($query); die;
        
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
    
}
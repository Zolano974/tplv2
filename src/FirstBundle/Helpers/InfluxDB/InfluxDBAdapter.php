<?php

namespace FirstBundle\Helpers\InfluxDB;

use GuzzleHttp\Client as HttpClient;
use InfluxDB\Client as InfluxClient;
use InfluxDB\Adapter\Http\Options as Options;
use InfluxDB\Adapter\Http\Reader as HttpReader;
use InfluxDB\Adapter\Http\Writer as HttpWriter;




class InfluxDBAdapter {
    

    private $httpClient;
    
    private $options;
    
    private $influxClient;

    private $httpReader;
    
    private $httpWriter;
    
    public function __construct($database = "test", $username = "zolano", $pwd = "zolano") {
        
        $this->httpClient = new HttpClient();
        
        $this->options = new Options();
        
        $this->options->setDatabase($database);
        
        $this->httpReader = new HttpReader( $this->httpClient, $this->options);
        $this->httpWriter = new HttpWriter( $this->httpClient, $this->options);
        
        $this->influxClient = new InfluxClient($this->httpReader, $this->httpWriter);
    }
    
    //on délègue ce comportement à l'élément encapsulé
    public function mark($name, array $values = []){
        
        return $this->influxClient->mark($name, $values);
    }
    
    //on délègue ce comportement à l'élément encapsulé
    public function query($query){
        
        return $this->influxClient->query($query);
    }
    
    
    
    
}

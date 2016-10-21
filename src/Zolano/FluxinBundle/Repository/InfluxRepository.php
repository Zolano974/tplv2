<?php

namespace Zolano\FluxinBundle\Repository;


use Zolano\FluxinBundle\Adapter\InfluxAdapter;
use Oft\Mvc\Application;
use Oft\Db\EntityQueryBuilder;
use Oft\Entity\BaseEntity;
use Oft\Mvc\Helper\Json;

use Doctrine\ORM\EntityManager;

class InfluxRepository{

    /**
     * @var InfluxAdapter       Adapter qui encapsule le client influxDB proposé par corley.
     */
    protected $influxAdapter;
    

    /**
     * @var EntityManager
     */
    protected $_em;    

    /**
     * @var Config : array of configuration
     */
    protected $config;

//    /**
//     * Connexion à la base de données
//     *
//     * @var Connection
//     */
//    protected $db;

    /**
     * InfluxRepository constructor.
     * @param string $host
     * @param string $database
        $this->user     = $config['idb_user'];
        $this->pwd      = $config['idb_pwd'];
        $this->host     = $config['idb_host'];
        $this->database = $config['idb_dbname'];* 
     */
    public function __construct(EntityManager $em, Array $config = array()){
  

        $this->config = (count($config)) ? $config : array(
            'idb_user'      => 'zolano',
            'idb_pwd'       => 'zolano',
            'idb_host'      => 'localhost',
            'idb_dbname'    => 'test',
        );

//        $this->db = $app->db;
        
        $this->_em = $em;

//        dump($this->config);die;

        $this->influxAdapter = new InfluxAdapter($this->config);
    }

    /**
     *  ############################################### Fonctions publiques Génériques ###############################################
     *  ############################################### Fonctions publiques Génériques ###############################################
     *  ############################################### Fonctions publiques Génériques ###############################################
     *  ############################################### Fonctions publiques Génériques ###############################################
     *  ############################################### Fonctions publiques Génériques ###############################################
     */

    /**
     * Fonction dédiée à effectuer nptequelle requête custom sur une base InfluxDB
     * @param $database
     * @param $query
     * @return mixed
     */
    public function selectQueryFromDatabase($database, $query){

        $this->influxAdapter->setDataBase($database);

        return $this->query($query);
    }


    /**
     * Fonction dédiée à requêter une collection influxDb dans une DB influx (pas beaucoup de subtilité)
     * @param $collection
     * @return mixed
     */
    public function selectAllFrom($database, $collection, $condition = ""){

        $where = ($condition !== "") ? " WHERE $condition " : "" ;

        $query = "SELECT * FROM $collection $where";

        return $this->selectQueryFromDatabase($database, $query);
    }


    /**
     * Fonction dédiée à la récupération de KPI ( un ou tous ) entre les bornes temporelles données en entrées
     * @param $database
     * @param $collection
     * @param $begin
     * @param $end
     * @param string $kpi_id
     * @return mixed
     */
    //select * from stats_Cecosane where time < '2016-05-01T00:00:00Z' AND time  > '2016-02-03T00:00:00Z' AND kpi_id = '133'
    public function selectMetrics($fields, $collection,  $begin, $end, $where = "", $groupby = "", $database = null){
        
        if($database === null) $database = $this->config['idb_dbname'];
//        
//        dump($begin);
//        dump($end);
        
        
         //on retire le décalage avec GMT aux bornes temporelles pour bien avoir les données de minuit à minuit
        $begin = $this->addGMToffset($begin . " 00:00:00", true);
        $end = $this->addGMToffset($end . " 00:00:00", true);
//        
//        dump("addGMToffset");
//        dump($begin);
//        dump($end);

        $where_condition = ($where !== "") ? " AND $where" : "" ;

        $query = "SELECT $fields FROM $collection WHERE time > '$begin' AND time <= '$end' $where_condition $groupby";
        
        dump($query);

        return $this->selectQueryFromDatabase($database, $query);
    }
    
    /** On délègue l'écriture de points à l'objet encapsulé : InfluxAdapter
     * @param $name
     * @param $values
     * @return mixed
     */
    public function mark($name, array $values = []){
        return $this->influxAdapter->mark($name, $values);
    }    
    
    
/**
 *  ############################################### Fonctions  Orientées TDB          ###############################################
 *  ############################################### ################################# ###############################################
 *  ############################################### ################################# ###############################################
 *  ############################################### ################################# ###############################################
 */


    /**     fonction dédiée à récupérer les données et les paramètres AMCHARTS pour un graphe                   FONCTION DU PLUS HAUT NIVEAU D'ORCHESTRATION !!!
     * @param $graph            GraphEntity correspondant au graphe (ou autre), muni d'une liste de KPIs, et d'une fonction getKpiList()
     * @param null $begin       date de début de la récupération au format "Y-m-d",;
     * @param null $end         date de fin de la récupération au format "Y-m-d"
     * @param bool $influx      mode de récupération (influx = true --> influxDB sinon MySQL)
     * @return array, composé de 4 champs :
     *                                      'graph'         =>  L'entité Graph passé en entrée
     *                                      'kpi_data'      =>  Les séries collectées, remaniées mais pas formatées pour AmCharts                                   (construction d'un tableau de données dans la vue)
     *                                      'chart_params'  =>  Les paramètres du graphe (en JSON), directement pluggable dans ungraphe amCharts :                  chart = AmCharts.makeChart("html_id", chart_params);
     *                                      'chart_data'    =>  Les données formatées pour Amcharts (en JSON), directement pluggable dans un graphe amChart :       chart.dataProvider = generateChartData(chart_data);
     */
    public function loadGraphData($graph, $begin = null, $end = null){
//
        //on récupère les données des KPI du graphe
        $series = $this->fetchGraphData($graph, $begin, $end);

        //on formate les données + convert JSON pour les rendre exploitables par les graphes AmCharts
        $chart_data = $this->formatData4AmCharts($series);

        //on obtient un objet JSON représentant les paramètres du graphe AmCharts, appliquables directement
        $chart_params = $this->getAmChartsJsonParams($series);

        //on ordonne le résultat dans un structure à 4 champs
        $output = array(
            'graph'         => $graph,
            'kpi_data'      => $series,
            'chart_data'    => $chart_data,
            'chart_params'  => $chart_params,
        );

        return $output;

    }


    /**  Renvoie l'ensemble des données pour le graphe passé en paramètre entre les bornes temporelles $begin et $end (toutes les données de tous les KPI du graphe)
     * @param $graph            Entity Graph (ou autre) : doit posséder une fonction getKpiList(), qui renvoie une liste d'objets KPI (munis à minima d'un ID, d'une COLLECTION, DATABASE et d'un NAME)
     * @param null $begin       Date de début au format YYYY-M-DD. Peut être nul auquel cas la valeur par défaut sera J - 1 mois.
     * @param null $end         Date de fin au format YYYY-M-DD. Peut être nul auquel cas la valeur par défaut sera le jour J.
     * @return array
     */
    public function fetchGraphData($graph, $begin = null, $end = null){

        $series = array();

        //on assure les valeurs de BEGIN et END
        $date = date("Y-m-d");
        //$last_week = date("Y-m-d",mktime(0,0,0,date("m"), date("d")-7, date("Y")));
        $last_month = date("Y-m-d",mktime(0,0,0,date("m")-1, date("d"), date("Y")));

        //si les dates ne sont pas renseignées, on met les bornes par défaut (de ya un mois à AJD)
        if($begin === null){ $begin = $last_month; }
        if($end === null){ $end = $date; }

        //pour chaque indicateur du graphe
        foreach($graph->getKpiList() as $kpi){

            $data =  $this->fetchKpiData($kpi, $begin, $end);

            if(count($data) > 0) $series[$kpi->name] = $data;
        }

        //on formatte le résultat dans un format facilement exploitable par la suite
        return $this->formatSeries($series);

    }

    /** Renvoie toutes les données stockées pour le KPI passé en paramètre, entre les bornes temporelles $begin et $end
     * @param $kpi          Structure possédant au moins :
     *                                                      - id
     *                                                      - name
     *                                                      - database
     *                                                      - collection
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function fetchKpiData($kpi, $begin, $end){

        //on récupère les infos importantes du KPI
        $id = $kpi->id;
        $database = $kpi->database;
        $collection = $kpi->collection;

        //on retire le décalage avec GMT aux bornes temporelles pour bien avoir les données de minuit à minuit
        $begin_offset = $this->addGMToffset($begin . " 00:00:00", true);
        $end_offset = $this->addGMToffset($end . " 00:00:00", true);

        //on construit et on écrit la requête
        $queryBuilder = $this->_em->createQueryBuilder();
        $query = $queryBuilder  ->select('value, time')
                                ->from($collection)
                                ->where("kpi_id = '$id' AND ( time >= '$begin_offset' AND time <= '$end_offset' )");

        $SqlQuery = $query->getSQL();

        //on execute la requete sur la database correspondante influxDB
        $brute_data = $this->selectQueryFromDatabase($database, $SqlQuery);

        //on formate le résultat pour que la structure renvoyée soit plus facile a manipuler par la suite
        $data = $this->Influx2Array($brute_data);

        return $data;
    }

    /** Convertit le format des données renvoyées par InfluxDB à un format array propice aux usages futurs de ces données
     * @param $brute_data       : données issues d'un requêtage direct de la base influxDB (avec selectQueryFromDatabase par exemple)
     * @return array            : données au format "Séries" utilisé en input pour le formatage ultérieur des données
     */
    private function Influx2Array($brute_data){

        $results = $brute_data['results'][0];

        $output = array();

        if(isset($results['series'])){
            $values = $brute_data['results'][0]['series'][0]['values'];

            foreach($values as $val){
                // 0 ==> time, 1 ==> value
                $date = $val[0];

                //                                      true : on applique l'offset GMT aux données extraites (pour gérer le fuseau horaire)
                $time = $this->formatRFC3339Date($date, true);

                $output[] = array(
                    'value'     => "$val[1]",
                    'date'     => $time,
                );
            }

        }
        return $output;

    }

    /** Transforme le format des séries  pour qu'il soit exploitable par la suite
     * @param $series       données renvoyees par fetchGraphData ou fetchKpiData
     * @return array        données dans un format exploitable pour AmCharts
     */
    private function formatSeries($series){

        $kpi_headers = array();
        $kpi_values = array();
        foreach($series as $kpi => $data){
            $kpi_headers[] = $kpi;
            foreach($data as $row){
                $kpi_values[$row['date']][$kpi] = $row['value'];
            }
        }

        ksort($kpi_values);
        ksort($kpi_headers);

        $kpi_data = array(
            'headers'   => $kpi_headers,
            'values'    => $kpi_values,
        );

        return $kpi_data;
    }

    /** Transforme les données d'input pour les faire correspondre au format d'entrée pour AmCharts
     * @param $series         Output de formatSeries()
     * @return JSON             Données en JSON au format attendu par les graphes AmCharts
     */
    private function formatData4AmCharts($series){

        $result = array();
        foreach($series['values'] as $date => $values){

            $row = array();
            $row['date'] = strtotime($date)*1000;
            foreach($values as $kpi => $val){
                $row[$kpi] = $val;
            }
            $result[] = $row;
        }

        return json_encode($result);
    }

    /** Restitue a partir des données en input un objet JSON de configuration apte à paramétrer le graphe AmCharts qui devra les tracer
     * @param $series       Données des KPI du graphe, servant de base pour la constitution du paramétrage (OUTPUT de formatSeries() )
     * @return JSON         Objet JSON de configuration du graphe AmChart dédié à tracer ces données
     */
    private function getAmChartsJsonParams($series){

        $params = $this->formatParams4amCharts($series);

        $json_params = $this->generateJsonParams4amcharts($params);

        return $json_params;

    }

    /** Transforme les données renvoyées par formatSerie() pour créer les params de configuration des courbes pour chaque série
     * @param $series       OUTPUT de formatSeries() : les données à tracer dans le graphe sont utilisées pour générer le paramétrage (nb de headers, nb de courbes, etc..)
     * @return array        Objet params, muni de la config AXES + courbes pour chacun des KPIs dans la collectio de données en INPUT
     */
    private function formatParams4amCharts($series){

        $colors = array(
            "#4d4d4d",
            "#5da5da",
            "#faa43a",
            "#60bd68",
            "#f17cb0",
            "#b2912f",
            "#b276b2",
            "#decf3f",
            "#f15854",
        );

        $params = array();

        $axis_offset = 5;
        $color_index = 0;
        foreach($series['headers'] as $head){

            $axis = new \stdClass();
            $axis->offset = $axis_offset;
            $axis->gridAlpha = 0;
            $axis->axisColor = (isset($colors[$color_index])) ? $colors[$color_index] : "#F60";
            $axis->axisThickness = 2;

            $chart = new \stdClass();
            $chart->title = $head;
            $chart->valueField = $head;
            $chart->bullet = "round";
            $chart->hideBulletsCount = 30;
            $chart->bulletBorderThickness = 1;

            $params[] = array(
                'axis'      => $axis,
                'chart'     => $chart,
            );

            $axis_offset += 30;
            $color_index++;
        }
        //        dump($params);
        return $params;

    }

    /** Utilise les params renvoyés par formatParams4amCharts(), et les utilise pour composer l'objet JSON qui va paramétrer ENTIEREMENT le graph amcharts
     * @param $params   OUTPUT de formatParams4amCharts(), objet contenant les paramètres pour tous les KPI à grapher
     * @return Json     Objet de conf prêt à l'emploi, appliquable directement sur un objet graph AmCharts lors de la fonction makeChart(html_id, json_params)
     */
    private function generateJsonParams4amcharts($params){

        //        dump($params); exit;

        $json_params = new \stdClass();

        $json_params->type = "serial";
        $json_params->theme = (isset($_SESSION['theme']) && $_SESSION['theme'] != "") ? $_SESSION['theme'] : "light";
        $json_params->legend = array(
            'marginLeft'        => 110,
            'useGraphSettings'  => true,
        );
        $json_params->chartScrollbar = array(
            'enabled'           => true,
            'scrollbarHeight'   => 20,
        );
        $json_params->valueScrollbar = array(
            'enabled'           => false,
            'scrollbarHeight'   => 10,
        );
        $json_params->chartCursor = array(
            'cursorPosition'            => 'mouse',
            'cursorAlpha'               => 0.1,
            'fullWidth'                 => true,
            'valueLineBalloonEnabled'   => true,
            "categoryBalloonDateFormat" => "DD MMM  HHh00",
        );
        $json_params->categoryField = "date";
        $json_params->categoryAxis = array(
            'parseDates'        => true,
            'axisColor'         => "#DADADA",
            'minPeriod'         => "hh",
            'twoLineMode'       => true,
            'minorGridEnabled'  => true,
        );
        $json_params->valueAxes = array(array(
            'id'                => 'v1',
            'axisColor'         => (isset($params[0]['axis'])) ? $params[0]['axis']->axisColor : "#333",
            'axisThickness'     => 2,
            'gridAlpha'         => 0,
            'axisAlpha'         => 1,
            'position'          => 'left',
            'precision'         => 3,
        ));
        $json_params->graphs = array();
        foreach($params as $param){

            $axis = $param['axis'];
            $chart = $param['chart'];

            $graph = array(
                "valueAxis"             => "v1",
                "bullet"                => "round",
                "title"                 => $chart->title,
                "bulletBorderThickness" => 1,
                "hideBulletsCount"      => 30,
                "valueField"            => $chart->valueField,
                "fillAlphas"            => 0,
            );

            $json_params->graphs[] = $graph;
        }

        $json_params->export = array(
            "enabled"   => true,
            "position"  => "bottom-right",
        );

        return json_encode($json_params);
    }


/**
 * ######################################## PRIVATE FUNCTIONS ########################################
 * ######################################## ################# ########################################
 * ######################################## ################# ########################################
 */

    /** transforme une date au format RFC 3339 en format YYYY-MM-DD hh:mm:ss, en tenant compte du décalage horaire (grâce à addGMToffset)
     * @param $date                 sous la forme : yyyy-mm-jjThh:ii:ssZ        Format RFC3339   ( https://www.ietf.org/rfc/rfc3339.txt )
     * @param bool $GMToffset       TRUE si on souhaite ajouter le GMT offset à la date transformée (i.e tenir compte du décalage horaire)
     * @return string               YYYY-MM-DD hh:mm:ss
     */
    private function formatRFC3339Date($date, $GMToffset = true){

        //on split par T central
        $date_row = explode('T' , $date);

        //on recupère yyyy-mm-dd
        $ymd = $date_row[0];

        $hms = $date_row[1];

        $date_origin = $ymd . " " . substr($hms,0,-1);

        $date_output = ($GMToffset) ? $this->addGMToffset($date_origin) : $date_origin ;

        return $date_output;
    }

    /** Ajoute les heures d'écart avec le fuseau GMT
     * @param $date                 date d'input, sur laquelle on veut appliquer le décalage avec GMT
     * @param bool $remove          définit si l'écart doit etre soustrait ou ajouté (pour les bornes temporelles il faut soustraire, pour l'extraction des données i faut rajouter)
     * @return date format          YYYY-MM-DD hh:mm:ss (avec l'offset appliqué)
     */
    private function addGMToffset($date, $remove = false){

        //on récupère le décalage GMT
        $decalage = date('P');                      //dump($decalage);
        $dec_hm = explode('+',$decalage)[1];        //dump($dec_hm);
        $dec_h =  substr($dec_hm, 0, -3);           //dump($dec_h);
        $int_dec_h = intval($dec_h);                //dump($int_dec_h);
        //on ,passe en timestamp
        $timestamp = strtotime($date);              //dump($timestamp);
        //on ajoute (ou retranche) l'offset au timestamp
        $timestamp = (!$remove) ? $timestamp + $int_dec_h * 60 * 60 : $timestamp - $int_dec_h * 60 * 60 ;
                                                    //dump($timestamp);
        //on formatte la date au format d'output
        $date_offset = date('Y-m-d H:i:s', $timestamp);
                                                    //dump($date_offset);

        return $date_offset;
    }


    /** On délègue le requêtage à l'objet encapsulé : InfluxAdapter
     * @param $query
     * @return mixed
     */
    private function query($query){
        return $this->influxAdapter->query($query);
    }
    



    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->_em;
    }    
    
}



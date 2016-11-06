<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FirstBundle\Repository\StatsRepository;
use FirstBundle\Entity\Item;
use FirstBundle\Form\ItemType;
//use FirstBundle\Helpers\InfluxDB\InfluxRepository;
use \Symfony\Component\Translation\Exception\NotFoundResourceException;

use Zolano\FluxinBundle\Repository\InfluxRepository;

class StatsController extends Controller {


    public function curveAction($workset_id, $mikbook){

        $user_id = 1;

        $request = Request::createFromGlobals();

        $begin_date = $request->request->get('begin_date', null);
        $end_date = $request->request->get('end_date', null);

        $workset = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Workset')
            ->fetchOneWithFields($workset_id);


        $itemDAO = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Item');

        $data_done = $itemDAO->loadWorksetData($user_id, $workset, $begin_date, $end_date);

        $data_mkb = $itemDAO->loadFieldsData($user_id, $workset->getId(), array(null), $begin_date, $end_date, true);

//        dump($data_done);die;

        return $this->render('FirstBundle:Stats:curve.html.twig', array(
            'workset'               => $workset,
            //items done
            'series_done'           => $data_done['series'],
            'chart_data_done'       => $data_done['chart_data'],
            'chart_params_done'     => $data_done['chart_params'],
            //items_mikbooked
            'series_mkb'           => $data_mkb['series'],
            'chart_data_mkb'       => $data_mkb['chart_data'],
            'chart_params_mkb'     => $data_mkb['chart_params'],
        ));

    }

    //      pour chaque matière

    //  - le nombre d'items total
    //  - le nombre d'items terminés
    //  - on en déduit le %

    //  le nombre de matières total

    //  le nombre de matières terminées

    //  nb d'items terminés / nb items total => % global du tour

    public function tourAction($workset_id){

        $request = Request::createFromGlobals();

        $tourDAO = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Tour');

        $user_id = 1;

        $workset = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Workset')
            ->fetchOneWithFields($workset_id);


        $statsDAO = new StatsRepository($this->getDoctrine()->getEntityManager());

        $last_iteration = $tourDAO->getLastTour($workset_id, $user_id);

        $iteration = $request->query->get('iteration', $last_iteration);

        $it_numbers = array();
        for($i = $last_iteration; $i > 0; $i--){
            $it_numbers[] = "$i";
        }
        sort($it_numbers);

        $stats = $statsDAO->getWorksetGlobal($workset, $iteration, $user_id);

        //si c'est un appel Ajax
        if($request->isXmlHttpRequest()){

            //traitement del'appel ajax

            #renvoyer du JSON

            $json_data= json_encode(array(
                'stats' => $stats,
                'iteration' => $iteration,
            ));

            $response = new Response($json_data);

            $response->headers->set('Content-Type','application/json');

            return $response;
        }
        else{
            return $this->render('FirstBundle:Stats:tour.html.twig', array(
                'workset_id'    => $workset_id,
                'iteration'     => $iteration,
                'it_numbers'    => $it_numbers,
                'stats'         => $stats,
            ));

        }



    }



    public function testAction() {

        $workset_id = 1;

        $user_id = 1;

        $begin  = "2016-10-21";
        $end    = "2016-10-30";
        $mikbook = false;

        $workset = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Workset')
            ->fetchOneWithFields($workset_id);


        $itemDAO = $this->getDoctrine()
            ->getManager()
            ->getRepository('FirstBundle:Item');


        $data = $itemDAO->loadWorksetData($user_id, $workset, $begin, $end, $mikbook, 'hour');


//        dump($data);die;

        return $this->render('FirstBundle:Item:test.html.twig', array(
            'workset'           => $workset,
            'series'            => $data['series'],
            'chart_data'        => $data['chart_data'],
            'chart_params'      => $data['chart_params'],
        ));


//        $influxDAO = new InfluxRepository($this->getDoctrine()->getManager());
//
//        $brute_data_done    = $influxDAO->getItemsDoneAggregate( '2016-10-07T00:00:00Z', 'now()', -1, 'day');
//        $brute_data_mkb     = $influxDAO->getItemsMkbAggregate( '2016-10-07T00:00:00Z', 'now()', -1, 'day');
//
//        dump($brute_data_done);
//        dump($brute_data_mkb); die;

    }

}

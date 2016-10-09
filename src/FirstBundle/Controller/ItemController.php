<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FirstBundle\Repository\ItemRepository;
use FirstBundle\Entity\Item;
use FirstBundle\Form\ItemType;
use FirstBundle\Helpers\InfluxDB\InfluxDBAdapter as InfluxClient;
use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class ItemController extends Controller {

    public function indexAction() {

        $em = $this->getDoctrine()->getManager();

        $itemDAO = $em->getRepository('FirstBundle:Item');

        $items = $itemDAO->findAll();

//        var_dump($items);

        return $this->render('FirstBundle:Item:index.html.twig', array(
                    'items' => $items,
        ));
    }

    public function viewAction($id) {

        $em = $this->getDoctrine()->getManager();

        $itemDAO = $em->getRepository('FirstBundle:Item');

        $item = $itemDAO->find($id);

        return $this->render('FirstBundle:Item:view.html.twig', array(
                    'item' => $item,
        ));
    }

    public function createAction() {

        //on créer un Workset et on lui donne des valeurs en dur pour l'instant
        $item = new Item();

        $form = $this->createForm(ItemType::class, $item);

        $request = Request::createFromGlobals();

        //si le formulaire a été soumis
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();

                //on persiste le item
                $em->persist($item);

                //on valide les transactions
                $em->flush();

                //onrenvoie vers la liste
                $url = $this->generateUrl('list_item');
                return $this->redirect($url);
            }
        }

        return $this->render('FirstBundle:Item:create-edit.html.twig', array(
                    'action' => 'create',
                    'form' => $form->createView(),
        ));
    }

    public function editAction($id) {

        $em = $this->getDoctrine()->getManager();

        $itemDAO = $em->getRepository('FirstBundle:Item');

        $item = $itemDAO->find($id);

        $form = $this->createForm(ItemType::class, $item);

        $request = Request::createFromGlobals();

        //si le form a été soumis
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            //si il est valide
            if ($form->isValid()) {

                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();

                //on persiste le item
                $em->persist($item);

                //on valide les transactions
                $em->flush();

                //onrenvoie vers la liste
                $url = $this->generateUrl('list_item');
                return $this->redirect($url);
            }
        }

        return $this->render('FirstBundle:Item:create-edit.html.twig', array(
                    'action' => 'edit',
                    'form' => $form->createView(),
        ));
    }

    public function deleteAction($id) {

        if ($id === null) {
            throw new NotFoundResourceException();
        }

        $request = Request::createFromGlobals();

        //si le form a été soumis
        if ($request->getMethod() == 'POST') {

            $id = $request->request->get('delete_id');

            $em = $this->getDoctrine()->getManager();

            $itemDAO = $em->getRepository('FirstBundle:Item');

            $item = $itemDAO->find($id);

            $em->remove($item);

            $em->flush();

            $url = $this->generateUrl('list_item');

            return $this->redirect($url);
        }

        return $this->render('FirstBundle:Item:delete.html.twig', array(
                    'id' => $id,
        ));
    }

    //fonction dédiée Ajax, pour le mikbookage des items
    public function mikbookAction() {

        $request = Request::createFromGlobals();

        if ($request->isXmlHttpRequest()) {

            $user_id = 1;

            $item_id = $request->request->get('item_id', null);

            $itemDAO = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('FirstBundle:Item');

            $itemDAO->mikbook($item_id, $user_id);

            //trigger une insertion influxDB

            $json_data = json_encode(array(
                'mikbooked'
            ));

            $response = new Response($json_data);

            $response->headers->set('Content-Type', 'application/json');

            return $response; //on utilise pas de template généralement en ajax
        }
    }

    //fonction dédiée Ajax, pour le mikbookage des items
    public function doneAction() {

        $request = Request::createFromGlobals();

        if ($request->isXmlHttpRequest()) {

            $user_id = 1;

            $item_id = $request->request->get('item_id', null);

            $iteration = $request->request->get('iteration', null);
            
            $field_id = $request->request->get('field_id', null);

            $itemDAO = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('FirstBundle:Item');

            $field_complete = $itemDAO->done($item_id, $iteration, $user_id);

            //trigger une insertion influxDB
            $influx_output = $this->markInfluxDBItemDone($item_id, $user_id, $field_id);

            $json_data = json_encode([
                    'field_complete'    => $field_complete,
                    'influx_output'     => $influx_output,
            ]);

            $response = new Response($json_data);

            $response->headers->set('Content-Type', 'application/json');

            return $response; //on utilise pas de template généralement en ajax
        }
    }

    //ecrit un point dans influxDB indiquant que cet item a été coché par cet utilisateur à ce moment
    private function markInfluxDBItemDone($item_id, $user_id, $field_id){
        
        $influx = new InfluxClient();

        $mark_array = [
            "tags" => [
                "item_id" => "$item_id" ,
                "field_id" => "$field_id",
                "user_id" => "$user_id",
            ],
            "points" => [
                [
                    "measurement" => "items_done",
                    "fields"    => [
                        "done" => 1,
                    ]
                ],
            ],
        ];    
        
        return $influx->mark($mark_array);
    }

    
    //ecrit un point dans influxDB indiquant que cet item a été miknooké par cet utilisateur à ce moment
    private function markInfluxDBItemMikbooked($item_id, $user_id, $field_id){
        
        $influx = new InfluxClient();

        $mark_array = [
            "tags" => [
                "item_id" => "$item_id" ,
                "field_id" => "$field_id",
                "user_id" => "$user_id",
            ],
            "points" => [
                [
                    "measurement" => "items_mikbooked",
                    "fields"    => [
                        "done" => 1,
                    ]
                ],
            ],
        ];    
        
        return $influx->mark($mark_array);
    }

    public function testAction() {


        $influx = new InfluxClient();


        $mark_array = [
            "tags" => [
                "item_id" => "1",
                "field_id" => "10",
                "user_id" => "1",
            ],
            "points" => [
                [
                    "measurement" => "items_done",
                    "fields"    => [
                        "done" => 1,
                    ]
                ],
            ],
        ];
        
        $result = $influx->mark($mark_array);
        dump("nik");
        dump($result); die;
    }    

}

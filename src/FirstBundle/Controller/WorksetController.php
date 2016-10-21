<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Entity\Workset;
use FirstBundle\Repository\WorksetRepository;
use FirstBundle\Form\WorksetType;

use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class WorksetController extends Controller
{
    public function indexAction()
    {
        
        $worksetDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Workset');
                            
                
                
        $worksets = $worksetDAO->findAll();
//        $worksets = $worksetDAO->fetchAllWithFields();

//        dump($worksets);die;

        return $this->render('FirstBundle:Workset:index.html.twig', array(
            'worksets'  => $worksets,
        ));        

    }
    
    public function viewAction($id){

        $workset = $this ->getDoctrine()
                    ->getManager()
                    ->getRepository('FirstBundle:Workset')
                    ->fetchOneWithFields($id);
//                    ->find($id);        
        
        return $this->render('FirstBundle:Workset:view.html.twig', array(
            'workset'  => $workset,
        ));        
                
    }
    
    public function workAction($id){
        
        $user_id = 1;
        
        $worksetDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Workset');
        
        $tourDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Tour');
        
        $items = $worksetDAO->getAllItemsDataByWorksetId($id, $user_id);
        
        $tours = $tourDAO->getAllByNumber($id , $user_id);
        
        $item_status = $worksetDAO->getItemStatus($id, $user_id);
        
//        dump("tours");
//        dump($tours);
//        dump("items");
//        dump($items);
//        dump("status");
//        dump($item_status);
//        die;
        
        return $this->render('FirstBundle:Workset:work.html.twig', array(
            'data'      => $items,
            'tours'     => $tours,
            'status'    => $item_status,
        ));               
    }
    
    public function testAction($id){
        
        dump("zob");die;
        
//        $user_id = 1;
//        
//        $item_id = 4;
//        
//        $iteration = 1;
//        
//        $itemDAO = $this ->getDoctrine()
//                            ->getManager()
//                            ->getRepository('FirstBundle:Item');   
//        
//        $itemDAO->allFieldItemsDone($item_id, $user_id, $iteration);
        
        
        
    }
    
    public function createNewTourAction(){
        
        $request = Request::createFromGlobals();
        
        $worksetDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Workset');
        
        $tourDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Tour');
        
        $iteration = $request->query->get('iteration');
        
        $user_id = 1;
        
        $workset_id = 1;
        
        $iteration = $tourDAO->getLastTour($workset_id, $user_id);
        
        $iteration++;
        
        $tourDAO->createTour($iteration, $workset_id, $user_id);
        
        $url = $this->generateUrl('work_workset', array( //omde la route tel que défini dans routing.yml
            'id' =>  $workset_id,
        ));
        
        return $this->redirect($url);         
    }
    
    public function createAction()
    {
        
        //on créer un Workset et on lui donne des valeurs en dur pour l'instant
        $workset = new Workset();

        $form = $this->createForm(WorksetType::class, $workset);
        
        $request = Request::createFromGlobals();
        
        //si le formulaire a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->handleRequest($request);
            
            if($form->isValid()){
                
                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();   
                
                //on persiste le workset
                $em->persist($workset);    
                
                //on valide les transactions
                $em->flush();  
                
                //onrenvoie vers la liste
                $url = $this->generateUrl('list_workset');
                return $this->redirect($url);                
            }
        }
        
        return $this->render('FirstBundle:Workset:create-edit.html.twig',array(
            'action'    => 'create',
            'form'      => $form->createView(),
        ));
 
    }
    
    public function editAction($id){
        
        $em = $this->getDoctrine()->getManager();
        
        $worksetDAO = $em->getRepository('FirstBundle:Workset');
        
        $workset = $worksetDAO->find($id);
        
        $form = $this->createForm(WorksetType::class, $workset);
        
        $request = Request::createFromGlobals();
        
        if($request->getMethod() == 'POST'){
            
            $form->handleRequest($request);
            
            if($form->isValid()){
                
                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();   
                
                //on persiste le workset
                $em->persist($workset);    
                
                //on valide les transactions
                $em->flush();  
                
                //onrenvoie vers la liste
                $url = $this->generateUrl('list_workset');
                return $this->redirect($url);                
            }
        }
        
        return $this->render('FirstBundle:Workset:create-edit.html.twig',array(
            'action'    => 'edit',
            'form'      => $form->createView(),
        ));
               
        
    }
    
    public function deleteAction($id){
        
        if($id === null){
            throw new NotFoundResourceException();
        }        
        
        $request = Request::createFromGlobals();
        
        if($request->getMethod() == 'POST'){
            
            $id = $request->request->get('delete_id');
            
            $em = $this->getDoctrine()->getManager();

            $worksetDAO = $em->getRepository('FirstBundle:Workset');

            $workset = $worksetDAO->find($id);

            $em->remove($workset);

            $em->flush();

            $url = $this->generateUrl('list_workset');

            return $this->redirect($url);                 
        }
        
        return $this->render('FirstBundle:Workset:delete.html.twig', array(
            'id'    => $id,
        ));        
        
    }
    
}

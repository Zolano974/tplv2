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
        
        $mikbooked = $worksetDAO->getMikbookedItems($id,$user_id);
        
//        dump("tours");
//        dump($tours);
//        dump("items");
//        dump($items);
//        dump("mikbooked");
//        dump($mikbooked);
//        die;
        
        return $this->render('FirstBundle:Workset:work.html.twig', array(
            'data'      => $items,
            'tours'     => $tours,
            'mikbooked' => $mikbooked,
        ));               
    }
    
    public function testAction($id){
        
        $request = Request::createFromGlobals();
        
        $worksetDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Workset');
        
        $tourDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Tour');
        
        $iteration = $request->query->get('iteration');
        
        $user_id = 1;
        
        $tourDAO->createTour($iteration, $id, $user_id);
        
        die;
        
//        $items = $worksetDAO->getAllItemsDataByWorksetId($id);
        
        return $this->render('FirstBundle:Workset:work.html.twig', array(
            'data'  => $data,
        ));               
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

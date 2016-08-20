<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        
        $items = $worksetDAO->getAllItemsDataByWorksetId($id);
        
        $tours = $tourDAO->getAllByNumber($id , $user_id);
        
//        dump($tours);
//        dump("trololo");
//        dump($items);
        
        return $this->render('FirstBundle:Workset:work.html.twig', array(
            'data'  => $items,
            'tours' => $tours,
        ));               
    }
    
    public function testAction($id){
        
        $worksetDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Workset');
        
        $tourDAO = $this ->getDoctrine()
                            ->getManager()
                            ->getRepository('FirstBundle:Tour');
        
        $tourDAO->createTour(1, $id);
        
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

        $form = $this->createForm(new WorksetType(), $workset);
        
        $request = $this->getRequest();
        
        //si le formulaire a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
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
        
        $form = $this->createForm(new WorksetType(), $workset);
        
        $request = $this->getRequest();     
        
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
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
        
        if($this->getRequest()->getMethod() == 'POST'){
            
            $id = $this->getRequest()->request->get('delete_id');

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

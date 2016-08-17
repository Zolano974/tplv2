<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Repository\FieldRepository;
use FirstBundle\Entity\Field;
use FirstBundle\Form\FieldType;

use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class FieldController extends Controller
{
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $fieldDAO = $em->getRepository('FirstBundle:Field');
        
        $fields=$fieldDAO->findAll();
        
//        var_dump($fields);
        
        // replace this example code with whatever you need
        return $this->render('FirstBundle:Field:test.html.twig', array(
            'fields'  => $fields,
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

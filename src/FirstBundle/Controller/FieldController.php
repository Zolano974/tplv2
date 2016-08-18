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
        
        $fields = $fieldDAO->findAll();
        
//        var_dump($fields);
        
        // replace this example code with whatever you need
        return $this->render('FirstBundle:Field:index.html.twig', array(
            'fields'  => $fields,
        ));        

    }
    
    public function createAction()
    {
        
        //on créer un Workset et on lui donne des valeurs en dur pour l'instant
        $field = new Field();

        $form = $this->createForm(new FieldType(), $field);
        
        $request = $this->getRequest();
        
        //si le formulaire a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
            if($form->isValid()){
                
                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();   
                
                //on persiste le field
                $em->persist($field);    
                
                //on valide les transactions
                $em->flush();  
                
                //onrenvoie vers la liste
                $url = $this->generateUrl('list_field');
                return $this->redirect($url);                
            }
        }
        
        return $this->render('FirstBundle:Field:create-edit.html.twig',array(
            'action'    => 'create',
            'form'      => $form->createView(),
        ));
 
    }
    
    public function editAction($id){
        
        $em = $this->getDoctrine()->getManager();
        
        $fieldDAO = $em->getRepository('FirstBundle:Field');
        
        $field = $fieldDAO->find($id);
        
        $form = $this->createForm(new FieldType(), $field);
        
        $request = $this->getRequest();     
        
        //si le form a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
            //si il est valide
            if($form->isValid()){
                
                //on récupère le EntityManager
                $em = $this->getDoctrine()->getManager();   
                
                //on persiste le field
                $em->persist($field);    
                
                //on valide les transactions
                $em->flush();  
                
                //onrenvoie vers la liste
                $url = $this->generateUrl('list_field');
                return $this->redirect($url);                
            }
        }
        
        return $this->render('FirstBundle:Field:create-edit.html.twig',array(
            'action'    => 'edit',
            'form'      => $form->createView(),
        ));
               
        
    }
    
    public function deleteAction($id){
        
        if($id === null){
            throw new NotFoundResourceException();
        }        
        
        //si le form a été soumis
        if($this->getRequest()->getMethod() == 'POST'){
            
            $id = $this->getRequest()->request->get('delete_id');

            $em = $this->getDoctrine()->getManager();

            $fieldDAO = $em->getRepository('FirstBundle:Field');

            $field = $fieldDAO->find($id);

            $em->remove($field);

            $em->flush();

            $url = $this->generateUrl('list_field');

            return $this->redirect($url);                 
        }
        
        return $this->render('FirstBundle:Field:delete.html.twig', array(
            'id'    => $id,
        ));        
        
    }
}

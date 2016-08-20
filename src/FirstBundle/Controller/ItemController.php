<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Repository\ItemRepository;
use FirstBundle\Entity\Item;
use FirstBundle\Form\ItemType;

use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class ItemController extends Controller
{
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $itemDAO = $em->getRepository('FirstBundle:Item');
        
        $items = $itemDAO->findAll();
        
//        var_dump($items);
        
        return $this->render('FirstBundle:Item:index.html.twig', array(
            'items'  => $items,
        ));        

    }
    
    
    public function viewAction($id){
        
        $em = $this->getDoctrine()->getManager();
        
        $itemDAO = $em->getRepository('FirstBundle:Item');
        
        $item = $itemDAO->find($id);
        
        return $this->render('FirstBundle:Item:view.html.twig', array(
            'item'  => $item,
        ));        
                
    }    
    
    public function createAction()
    {
        
        //on créer un Workset et on lui donne des valeurs en dur pour l'instant
        $item = new Item();

        $form = $this->createForm(new ItemType(), $item);
        
        $request = $this->getRequest();
        
        //si le formulaire a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
            if($form->isValid()){
                
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
        
        return $this->render('FirstBundle:Item:create-edit.html.twig',array(
            'action'    => 'create',
            'form'      => $form->createView(),
        ));
 
    }
    
    public function editAction($id){
        
        $em = $this->getDoctrine()->getManager();
        
        $itemDAO = $em->getRepository('FirstBundle:Item');
        
        $item = $itemDAO->find($id);
        
        $form = $this->createForm(new ItemType(), $item);
        
        $request = $this->getRequest();     
        
        //si le form a été soumis
        if($request->getMethod() == 'POST'){
            
            $form->bind($request);
            
            //si il est valide
            if($form->isValid()){
                
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
        
        return $this->render('FirstBundle:Item:create-edit.html.twig',array(
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

            $itemDAO = $em->getRepository('FirstBundle:Item');

            $item = $itemDAO->find($id);

            $em->remove($item);

            $em->flush();

            $url = $this->generateUrl('list_item');

            return $this->redirect($url);                 
        }
        
        return $this->render('FirstBundle:Item:delete.html.twig', array(
            'id'    => $id,
        ));        
        
    }
}

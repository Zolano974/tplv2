<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Repository\WorksetRepository;
use FirstBundle\Entity\Workset;

use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class WorksetController extends Controller
{
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $worksetDAO = $em->getRepository('FirstBundle:Workset');
        
        $worksets=$worksetDAO->findAll();
        
//        var_dump($worksets);
        
        // replace this example code with whatever you need
        return $this->render('FirstBundle:Workset:test.html.twig', array(
            'worksets'  => $worksets,
        ));        

    }
    
    public function createAction()
    {
        //on récupère le EntityManager
        $em = $this->getDoctrine()->getManager();
        
        //on créer un Workset et on lui donne des valeurs en dur pour l'instant
        $workset = new Workset();
        
        $workset->setName('Workset en Dur');
        $workset->setGeneric(0);
        $workset->setDescription('En Dur pour faire des Tests sans formulaire');
        
        //on persiste le workset
        $em->persist($workset);
        
        //on valide les transactions
        $em->flush();
        
        $url = $this->generateUrl('list_workset');
        
        return $this->redirect($url);
 
    }
    
    public function deleteAction($id){
        
        if($id === null){
            throw new NotFoundResourceException();
        }        
        
        if($this->getRequest()->getMethod() == 'POST'){
            
            $id = $this->getRequest()->request->get('delete_id');

            $em = $this->getDoctrine()->getEntityManager();

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

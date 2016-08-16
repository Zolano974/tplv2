<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Repository\WorksetRepository;

class WorksetController extends Controller
{
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $worksetDAO = $em->getRepository('FirstBundle:Workset');
        
        $worksets=$worksetDAO->findAll();
        
        var_dump($worksets);
        
        // replace this example code with whatever you need
        return $this->render('FirstBundle:Workset:test.html.twig', array(
            'worksets'  => $worksets,
        ));        

    }
    
    public function sayAction($thing)
    {
 
    }
}

<?php

namespace FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FirstBundle\Repository\KanbanRepository;

use \Symfony\Component\Translation\Exception\NotFoundResourceException;

class KanbanController extends Controller
{
    
    //action qui affiche le kanban d'unematiere pour un tour donné
    public function kanbanAction($field_id, $iteration){
        
        $user_id = 1;
        
        if($field_id === null || $iteration === null){
            throw new NotFoundResourceException();
        }       
        
        
        $em = $this->getDoctrine()->getManager();
        
        $itemDAO = $em->getRepository('FirstBundle:Item');
        $fieldDAO = $em->getRepository('FirstBundle:Field');
        $kanbanDAO = $em->getRepository('FirstBundle:Kanban');
        
//        dump($kanbanDAO);
        
        $field = $fieldDAO->find($field_id);
        
        $items = $kanbanDAO->fetchAllItemsByFieldAndIteration($field_id, $iteration, $user_id);
        
        $items_step = $this->groupItemsBySteps($items);
        
        return $this->render('FirstBundle:Kanban:kanban.html.twig', array(
            'items_notdone'     => $items_step[0],
            'items_todo'        => $items_step[1],
            'items_ongoing'     => $items_step[2],
            'items_done'        => $items_step[3],
            'field'             => $field,
            'iteration'         => $iteration,
        ));             
        
        
        
    }
    
    private function groupItemsBySteps($items){
        $output = array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
        );
        
        foreach($items as $row){
            
            $item = new \stdClass();
            $item->id = $row['item_id'];
            $item->name = $row['item_name'];
            $item->step = $row['step'];
            
            $output[$item->step][] = $item;
            
        }
        
        return $output;
    }
                   
}

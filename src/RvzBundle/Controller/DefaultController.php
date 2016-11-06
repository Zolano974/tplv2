<?php

namespace RvzBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RvzBundle:Default:index.html.twig');
    }
}

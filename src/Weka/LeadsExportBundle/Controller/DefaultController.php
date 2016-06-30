<?php

namespace Weka\LeadsExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WekaLeadsExportBundle:Default:index.html.twig', array('name' => $name));
    }


}

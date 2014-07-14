<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/testing")
 * @Cache(expires="tomorrow")
 */
class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TellawLeadsFactoryBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * @Route("/test/", name="_index")
     * @Template()
     */
    public function testAction()
    {

        return array( );
    }

}

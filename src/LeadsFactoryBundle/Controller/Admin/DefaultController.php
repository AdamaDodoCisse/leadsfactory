<?php

namespace LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use LeadsFactoryBundle\Shared\CoreController;

/**
 *
 */
class DefaultController extends CoreController
{

    public function __construct()
    {
        parent::__construct();

    }

    /**
     *
     * @Route("/index.html", name="_index")
     * @Route("/")
     * @Template()
     *
     */
    public function indexAction(Request $request)
    {

        return $this->render( 'react/index.html.twig', [] );
    }


}

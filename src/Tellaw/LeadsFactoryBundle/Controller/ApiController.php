<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiController extends Controller
{

    /**
     * @Route("/lead/{id}/{key}")
     */
    public function getLeadAction($id, $apikey=null)
    {
	    $formUtils = $this->get("form_utils");

		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

	    if(!is_null($lead)){
		    $data = $lead->getData();

		    //check key
		    if(!$formUtils->checkApiKey($lead->getForm(), $apikey)){
			    //throw new AccessDeniedHttpException('Invalid form key');
		    }
	    }else{
		    $data = '{}';
	    }

		$response =  new Response($data);
	    $response->headers->set('content-type', 'Content-Type: application/json');

	    return $response;

    }

}
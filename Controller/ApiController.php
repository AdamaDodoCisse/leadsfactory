<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tellaw\LeadsFactoryBundle\Entity\ClientEmail;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Symfony\Component\HttpFoundation\JsonResponse;

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

	        $data = json_decode($data, true);
	        $data['created_at'] = $lead->getCreatedAt();
	        $data = json_encode($data);

            //check key
            if(!$formUtils->checkApiKey($lead->getForm(), $apikey)){
                //throw new AccessDeniedHttpException('Invalid form key');
            }
        }else{
            $data = '{}';
        }

        $response =  new Response($data);
        $response->headers->set('content-type', 'application/json');

        return $response;

    }

    /**
     * Marks the e-mail as validated and updates the corresponding exports status
     *
     * @Route("/email/validate")
     * @Method("POST")
     */
    public function validateEmailAction(Request $request)
    {
        $entity_manager = $this->container->get('doctrine')->getEntityManager();

        $email_repository = $this->container->get('leadsfactory.client_email_repository');
        $email = $request->request->get('email');
        if (empty($email)) {
            $response = new Response();
            $response->setStatusCode(400, 'email parameter is mandatory');
            return $response;
        }
        /** @var ClientEmail|null $client_email */
        $client_email = $email_repository->findOneByEmail($email);

        if (is_null($client_email)) {
            $client_email = new ClientEmail();
            $client_email->setEmail($email);
            $entity_manager->persist($client_email);
        }

        if (is_null($client_email->getValidation())) {
            $client_email->setValidation(new \DateTime());

            $export_repository = $this->container->get('leadsfactory.export_repository');
            $exports = $export_repository->findByEmailWaitingValidation($email);
            /** @var Export $export */
            foreach ($exports as $export) {
                $export->setStatus(ExportUtils::$_EXPORT_NOT_PROCESSED);
            }

            $response_status = 'Validated '.$email;
        } else {
            $response_status = $email.' already valid';
        }

        $entity_manager->flush();

        return new JsonResponse(array('status' => $response_status));
    }

	/**
	 * Retrieve leads based on creation date
	 *
	 * @Route("/leads")
	 */
	public function getLeadsAction(Request $request)
	{
		$args = array(
			'datemin'   => array('date' => $request->query->get('datemin')),
			'datemax'   => array('date' => $request->query->get('datemax'))
		);

		$leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getLeads($args);

		if(!empty($leads)){
			$result = array();
			foreach($leads as $lead){
				$result['leads'][] = array(
					'id'        => $lead->getId(),
					'data'      => $lead->getData(),
					'status'    => $lead->getStatus(),
					'form'      => $lead->getForm()->getName(),
					'created_at'=> $lead->getCreatedAt()

				);
			}
			$result = json_encode($result);
		}else{
			$result = '{}';
		}

		$response =  new Response($result);
		$response->headers->set('content-type', 'application/json');

		return $response;
	}
}

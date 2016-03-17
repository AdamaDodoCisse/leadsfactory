<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tellaw\LeadsFactoryBundle\Entity\ClientEmail;
use Tellaw\LeadsFactoryBundle\Entity\Export;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tellaw\LeadsFactoryBundle\Entity\Leads;

class ApiController extends CoreController
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
	        $data['lfFormId'] = $lead->getForm()->getId();
	        $data['form'] = $lead->getForm()->getCode();
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
     *
     * TODO : Securisation methode appel
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

            $entity_manager->flush();

            $response_status = 'Validated '.$email;
        } else {
            $response_status = $email.' already valid';
        }

        return new JsonResponse(array('status' => $response_status));
    }

	/**
	 * Retrieve leads based on creation date
	 * TODO : getLeadsAction sécurisation des données
     * @deprecated au profit getLeadsServiceAction
	 * @Route("/leads")
	 */
	public function getLeadsAction(Request $request)
	{
		$logger = $this->get('logger');

		$args = array(
			'datemin'   => $request->query->get('datemin'),
			'datemax'   => $request->query->get('datemax'),
			'email'     => $request->query->get('email'),
			'form'      => $request->query->get('form'),
		);

		$scope = !is_null($request->query->get('scope')) ? $request->query->get('scope') : null;
		if(!is_null($scope)){
			$scope = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Scope')->findOneByCode($scope);
			$args['scope'] = $scope->getId();
		}

		$form_code = $request->query->get('form_code');
		if(!is_null($form_code)){
			$form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($form_code);
			if(!empty($form))
				$args['form'] = $form->getId();
		}

		$leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getLeads($args);

		if(!empty($leads)){

			$result = array();
			foreach($leads as $lead){

				$data = json_decode($lead->getData());
				$scope = !is_null($lead->getForm()->getScope()) ? $lead->getForm()->getScope()->getCode() : null;
                
				// libellé de la fonction
				$functionListCode = ($scope == 'ti') ? 'ti_fonction' : 'fonction';
				if(!empty($data->fonction)) {
					$data->fonction = $this->getDoctrine()->getRepository( 'TellawLeadsFactoryBundle:ReferenceListElement' )->getNameUsingListCode( $functionListCode, $data->fonction );
				}

				//libellé ville
				if(!empty($data->ville_id)){
					$data->ville = $this->getDoctrine()->getRepository( 'TellawLeadsFactoryBundle:ReferenceListElement' )->getNameUsingListCode( 'ville', $data->ville_id );
				}

				$result['leads'][] = array(
					'id'        => $lead->getId(),
					'data'      => $data,
					'status'    => $lead->getStatus(),
					'form'      => $lead->getForm()->getName(),
					'form_id'   => $lead->getForm()->getId(),
					'scope'     => $scope,
					'key'       => $this->get("form_utils")->getApiKey($lead->getForm()),
					'created_at'=> $lead->getCreatedAt()->format('Y-m-d')
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

    /**
     * Retrieve leads based on creation date
     * TODO : getLeadsAction sécurisation des données
     * @deprecated au profit getLeadsServiceAction
     * @Route("/getLeads")
     */
    public function getLeadsServiceAction(Request $request)
    {
        $logger = $this->get('logger');

        $utils = $this->get ("form_utils");

        $args = array(
            'datemin'   => $request->query->get('datemin'),
            'datemax'   => $request->query->get('datemax'),
            'email'     => $request->query->get('email'),
        );

        $scope = !is_null($request->query->get('scope')) ? $request->query->get('scope') : null;
        if(!is_null($scope)){
            $scope = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Scope')->findOneByCode($scope);
            $args['scope'] = $scope->getId();
        }

        $form_code = $request->query->get('form_code');
        if(!is_null($form_code)){
            $form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($form_code);
            if(!empty($form))
                $args['form'] = $form->getId();
        }

        $leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getLeads($args);

        $formsFieldsDescriptions = array();

        if(!empty($leads)){

            $result = array();
            foreach($leads as $lead){

                $data = json_decode($lead->getData());
                $scope = !is_null($lead->getForm()->getScope()) ? $lead->getForm()->getScope()->getCode() : null;

                // Remplacement des id de listes par les valeurs
                if (!isset( $formsFieldsDescriptions[$lead->getForm()->getId()] )) {
                    $fields = $utils->getReferenceListsFieldsByFormId($lead->getForm()->getId());
                    $formsFieldsDescriptions[$lead->getForm()->getId()] = $fields;
                } else {
                    $fields = $formsFieldsDescriptions[$lead->getForm()->getId()];
                }

                foreach ($fields as $id => $field) {

                    // Create a field [formid]_label = the label of reference list
                    $fieldIdName = $id."_label";

                    // Put value in the field [formid] = value
                    $data->$fieldIdName =    $this   ->getDoctrine()
                                            ->getRepository( 'TellawLeadsFactoryBundle:ReferenceListElement' )
                                            ->getNameUsingListCode(
                                                                    $field["attributes"]["data-list"],
                                                                    $data->$id
                                                                    );
                }

                $result['leads'][] = array(
                    'id'        => $lead->getId(),
                    'data'      => $data,
                    'status'    => $lead->getStatus(),
                    'form'      => $lead->getForm()->getName(),
                    'form_id'   => $lead->getForm()->getId(),
                    'scope'     => $scope,
                    'key'       => $this->get("form_utils")->getApiKey($lead->getForm()),
                    'created_at'=> $lead->getCreatedAt()->format('Y-m-d')
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


	/**
	 * Enregistre une DI
	 * TODO : postLeadAction Securisation des données
	 * @Route("/lead/post")
	 * @Method("POST")
	 */
	public function postLeadAction(Request $request)
	{
		$exportUtils = $this->get('export_utils');
		$logger = $this->get('logger');
        $searchUtils = $this->get('search.utils');

		$logger->info('API post lead');

		$data = $request->getcontent();
		$logger->info($data);
		$data = json_decode($data, true);

		try{
			$form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($data['formCode']);

			$data = $this->get('form_utils')->preProcessData($form->getId(), $data);
			$jsonContent = json_encode($data);

			$leads = new Leads();
			$leads->setFirstname(@$data['firstName']);
			$leads->setLastname(@$data['lastName']);
			$leads->setData($jsonContent);
			$leads->setLog("leads importée le : ".date('Y-m-d h:s'));
			$leads->setUtmcampaign(@$data["utmcampaign"]);
			$leads->setForm($form);
			$leads->setTelephone(@$data["phone"]);
			$leads->setEmail(@$data['email']);

			$status = $exportUtils->hasScheduledExport($form->getConfig()) ? $exportUtils::$_EXPORT_NOT_PROCESSED : $exportUtils::$_EXPORT_NOT_SCHEDULED;
			$leads->setStatus($status);

			$leads->setCreatedAt( new \DateTime() );

			$em = $this->getDoctrine()->getManager();
			$em->persist($leads);
			$em->flush();

            // Index leads on search engine
            $leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leads->getId());
            $searchUtils->indexLeadObject($leads_array, $leads->getForm()->getScope()->getCode());

			// Create export job(s)
			if($status == $exportUtils::$_EXPORT_NOT_PROCESSED){
				$exportUtils->createJob($leads);
			}

			return new Response(1);

		}catch(Exception $e){
			$logger->error($e->getMessage());
			return new Response(0);
		}
	}
}

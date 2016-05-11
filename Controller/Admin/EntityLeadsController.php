<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Entity\DataDictionnaryRepository;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\LeadsComment;
use Tellaw\LeadsFactoryBundle\Entity\Users;
use Tellaw\LeadsFactoryBundle\Form\Type\LeadsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;
use Swift_Message;

/**
 * @Route("/entity")
 */
class EntityLeadsController extends CoreController
{

    public function __construct () {

		PreferencesUtils::registerKey( "CORE_LEADSFACTORY_EMAIL_SENDER",
			"Email used by the lead's factory as sender in emails",
			PreferencesUtils::$_PRIORITY_OPTIONNAL
		);

        parent::__construct();
    }

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/userList/{page}/{limit}/{keyword}", name="_leads_userList")
	 */
	public function indexUserAction(Request $request, $page=1, $limit=25, $keyword='')
	{

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		$filterForm = $this->getLeadsFilterForm();
		$filterForm->handleRequest($request);

		if ($filterForm->isValid()) {
			$filterParams = $filterForm->getData();
			$filterParams["user"] = $this->getUser();
			$filterParams["owner"] = $this->getUser();
			$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
		}else{
			$filterParams =  array ('user'=>$this->getUser(), 'owner'=>$this->getUser());
			$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
		}

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:userList.html.twig',
			array(
				'elements'      => $list['collection'],
				'pagination'    => $list['pagination'],
				'limit_options' => $list['limit_options'],
				'filters_form'  => $filterForm->createView(),
				'export_form'   => $this->getReportForm($filterParams)->createView()
			)
		);
	}

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/leads/list/{page}/{limit}/{keyword}", name="_leads_list")
     */
    public function indexAction(Request $request, $page=1, $limit=25, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $filterForm = $this->getLeadsFilterForm();
	    $filterForm->handleRequest($request);

	    if ($filterForm->isValid()) {
		    $filterParams = $filterForm->getData();
			$filterParams["user"] = $this->getUser();
		    $list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }else{
			$filterParams =  array ('user'=>$this->getUser());
		    $list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Leads:list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
	            'filters_form'  => $filterForm->createView(),
	            'export_form'   => $this->getReportForm($filterParams)->createView()
            )
        );
    }

    /**
     * @Route("/leads/edit/{id}", name="_leads_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

		$leadDetail = json_decode($lead->getData(), true);
		unset($leadDetail["firstname"]);
		unset($leadDetail["firstName"]);
		unset($leadDetail["lastName"]);
		unset($leadDetail["lastname"]);
		unset($leadDetail["email"]);

		if ($lead->getUser() != null) {
			$assignUser = ucfirst($lead->getUser()->getFirstName()). " " .ucfirst($lead->getUser()->getLastName());
		} else {
			$assignUser = "";
		}


        return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit.html.twig', array(  'lead' => $lead,
																								'leadDetail' => $leadDetail,
																								'assignUser' => $assignUser,
                                                                                             'title' => "Edition d'un leads"));
    }

	/**
	 * @Route("/leads/comments/add", name="_leads_add_comment_fragment")
	 * @Secure(roles="ROLE_USER")
	 */
	public function addCommentAjaxAction ( Request $request ) {

		$id = $request->request->get("id");
		$commentText = $request->request->get("comment");

		if (trim($id) != "" && $id != 0) {

			$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

			$user = $this->getUser();

			$comment = new LeadsComment();
			$comment->setCreatedAt(new \DateTime());
			$comment->setUser( $user );
			$comment->setLead( $lead );
			$comment->setComment( $commentText );

			$em = $this->getDoctrine()->getManager();
			$em->persist($comment);
			$em->flush();

			// Adding an entry to history
			$this->get("history.utils")->push ( "Ajout d'un commentaire ", $this->getUser(), $lead );

		} else {
			throw new \Exception ("Id is not defined");
		}

		return new Response('Enregistré');

	}

	/**
	 * @Route("/leads/status/list/ajax", name="_leads_list_status_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function statusListLoadAjaxAction ( Request $request ) {

		//$listCode = $request->request->get("listCode");
		$listCode = "leads-status";
		$scopeId = $request->request->get ("scopeId");

		/** @var DataDictionnaryRepository $dataDictionnary */
		$dataDictionnary = $this->get("leadsfactory.datadictionnary_repository");
		$dataDictionnaryId = $this->get("leadsfactory.datadictionnary_repository")->findByCodeAndScope( $listCode, $scopeId );

		$elements = $dataDictionnary->getElementsByOrder( $dataDictionnaryId, "rank", "ASC" );

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit-status-list-ajax.html.twig',
			array(  'elements' => $elements));

	}

	/**
	 * @Route("/leads/status/assign", name="_leads_status_assign_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function affectStatusToLead( Request $request ) {

		$id = $request->request->get("id");
		$leadId = $request->request->get("leadId");
		$listValue = $request->request->get ("listValue");

		/** @var Leads $lead*/
		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($leadId);
		$lead->setWorkflowStatus( $id );

		$em = $this->getDoctrine()->getManager();
		$em->persist($lead);
		$em->flush();

		// Adding an entry to history
		$this->get("history.utils")->push ( "Changement de status pour : " . $listValue, $this->getUser(), $lead );

		$prefUtils = $this->get('preferences_utils');
		$leadsUrl = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $lead->getForm()->getScope()->getId());
		/**
		 * Send notification to a user
		 * Mail is sent to the user owner of the lead
		 */
		$result = $this->sendNotificationEmail (  	"Changement de status pour une LEAD",
			"Un utilisateur vient de modifier le status associé à une lead.",
			$this->getUser(),
			"Le ".date ("d/m/Y à h:i"). " ".ucfirst($this->getUser()->getFirstName()). " ".ucfirst($this->getUser()->getLastName()). " vient de modifier le status de la lead : ".$leadId." pour le passer à '".$listValue."'"  ,
			$leadsUrl,
			$leadsUrl,
			$lead->getForm()->getScope()->getId()
		);


		return new Response('ok');

	}

	/**
	 * @Route("/leads/history/list/ajax", name="_leads_list_history_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function historyListLoadAjaxAction ( Request $request ) {

		$leadsId = $request->request->get("leadId");

		/** @var DataDictionnaryRepository $dataDictionnary */
		$historyElements = $this->get("leadsfactory.leads_history_repository")->getHistoryForLead( $leadsId );

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit-history-list-ajax.html.twig',
			array(  'elements' => $historyElements));

	}

	/**
	 * @Route("/leads/export/list/ajax", name="_leads_list_exports_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function exportsListLoadAjaxAction ( Request $request ) {

		$leadsId = $request->request->get("leadId");

		/** @var DataDictionnaryRepository $dataDictionnary */
		$elements = $this->get("leadsfactory.export_repository")->getForLeadID( $leadsId );

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit-export-list-ajax.html.twig',
			array(  'elements' => $elements));

	}

	/**
	 * @Route("/leads/type/list/ajax", name="_leads_list_type_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function statusTypeLoadAjaxAction ( Request $request ) {

		$listCode = "leads-type";
		$scopeId = $request->request->get ("scopeId");

		/** @var DataDictionnaryRepository $dataDictionnary */
		$dataDictionnary = $this->get("leadsfactory.datadictionnary_repository");
		$dataDictionnaryId = $this->get("leadsfactory.datadictionnary_repository")->findByCodeAndScope( $listCode, $scopeId );

		$elements = $dataDictionnary->getElementsByOrder( $dataDictionnaryId, "rank", "ASC" );

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit-type-list-ajax.html.twig',
			array(  'elements' => $elements));

	}

	/**
	 * @Route("/leads/type/assign", name="_leads_type_assign_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function affectTypeToLead( Request $request ) {

		$id = $request->request->get("id");
		$leadId = $request->request->get("leadId");
		$listValue = $request->request->get ("listValue");

		/** @var Leads $lead*/
		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($leadId);
		$lead->setWorkflowType( $id );

		$em = $this->getDoctrine()->getManager();
		$em->persist($lead);
		$em->flush();

		// Adding an entry to history
		$this->get("history.utils")->push ( "Changement de type pour : " . $listValue, $this->getUser(), $lead );

		$prefUtils = $this->get('preferences_utils');
		$leadsUrl = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $lead->getForm()->getScope()->getId());
		/**
		 * Send notification to a user
		 * Mail is sent to the user owner of the lead
		 */
		$result = $this->sendNotificationEmail (  	"Changement de type pour une LEAD",
			"Un utilisateur vient de modifier le type associé à une lead.",
			$this->getUser(),
			"Le ".date ("d/m/Y à h:i"). " ".ucfirst($this->getUser()->getFirstName()). " ".ucfirst($this->getUser()->getLastName()). " vient de modifier le type de la lead : ".$leadId." pour le passer à '".$listValue."'"  ,
			$leadsUrl,
			$leadsUrl,
			$lead->getForm()->getScope()->getId()
		);


		return new Response('ok');

	}

	/**
	 * @Route("/leads/theme/list/ajax", name="_leads_list_theme_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function themeListLoadAjaxAction ( Request $request ) {

		$listCode = "leads-theme";
		$scopeId = $request->request->get ("scopeId");

		/** @var DataDictionnaryRepository $dataDictionnary */
		$dataDictionnary = $this->get("leadsfactory.datadictionnary_repository");
		$dataDictionnaryId = $this->get("leadsfactory.datadictionnary_repository")->findByCodeAndScope( $listCode, $scopeId );

		$elements = $dataDictionnary->getElementsByOrder( $dataDictionnaryId, "rank", "ASC" );

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit-theme-list-ajax.html.twig',
			array(  'elements' => $elements));

	}

	/**
	 * @Route("/leads/theme/assign", name="_leads_theme_assign_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function affectThemeToLead( Request $request ) {

		$id = $request->request->get("id");
		$leadId = $request->request->get("leadId");
		$listValue = $request->request->get ("listValue");

		/** @var Leads $lead*/
		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($leadId);
		$lead->setWorkflowTheme( $id );

		$em = $this->getDoctrine()->getManager();
		$em->persist($lead);
		$em->flush();

		// Adding an entry to history
		$this->get("history.utils")->push ( "Changement de thème pour : " . $listValue, $this->getUser(), $lead );

		$prefUtils = $this->get('preferences_utils');
		$leadsUrl = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $lead->getForm()->getScope()->getId());
		/**
		 * Send notification to a user
		 * Mail is sent to the user owner of the lead
		 */
		$result = $this->sendNotificationEmail (  	"Changement de thème pour une LEAD",
													"Un utilisateur vient de modifier le thème associé à une lead.",
													$this->getUser(),
													"Le ".date ("d/m/Y à h:i"). " ".ucfirst($this->getUser()->getFirstName()). " ".ucfirst($this->getUser()->getLastName()). " vient de modifier le thème de la lead : ".$leadId." pour le passer à '".$listValue."'"  ,
													$leadsUrl,
													$leadsUrl,
													$lead->getForm()->getScope()->getId()
			);

		if ($result)
			return new Response('ok');
		else
			throw new Exception("Problem sending mail");

	}

	/**
	 * @Route("/leads/users/search", name="_leads_users_search_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function searchUserLeadAction ( Request $request ) {

		$term = $request->query->get("term");
		$users = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->getList (1, 10, $term );

		$responseUsers = array();

		foreach ( $users as $user ) {
			$responseUsers[] = array ( "label" => ucfirst($user->getFirstName()). " ". ucfirst($user->getLastName()), "value" => $user->getId() );
		}

		return new Response(json_encode( $responseUsers ));

	}

	/**
	 * @Route("/leads/users/assign", name="_leads_users_assign_ajax")
	 * @Secure(roles="ROLE_USER")
	 */
	public function affectLeadToUser( Request $request ) {

		$id = $request->request->get("id");
		$leadId = $request->request->get("leadId");
		$user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find ( $id );

		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($leadId);
		$lead->setUser( $user );

		$em = $this->getDoctrine()->getManager();
		$em->persist($lead);
		$em->flush();

		// Adding an entry to history
		$this->get("history.utils")->push ( "Attribution à : " . ucfirst($user->getFirstName()). " ". ucfirst($user->getLastName()), $this->getUser(), $lead );

		$prefUtils = $this->get('preferences_utils');
		$leadsUrl = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $lead->getForm()->getScope()->getId());

		/**
		 * Send notification to a user
		 * Mail is sent to the user owner of the lead
		 */
		$result = $this->sendNotificationEmail (  	"Changement d'affectation pour la LEAD #".$leadId,
			"Un utilisateur vient de modifier l'affectation d'une lead.",
			$user,
			"Le ".date ("d/m/Y à h:i"). " ".ucfirst($this->getUser()->getFirstName()). " ".ucfirst($this->getUser()->getLastName()). " vient de vous assigner la lead : ".$leadId  ,
			$leadsUrl,
			$leadsUrl,
			$lead->getForm()->getScope()->getId()
		);


		return new Response('ok');

	}

	/**
	 * @Route("/leads/comments/load", name="_leads_load_comments_fragment")
	 * @Secure(roles="ROLE_USER")
	 */
	public function loadCommentsAjaxAction ( Request $request ) {

		$id = $request->request->get("leadId");

		if (trim($id) != "" && $id != 0) {
			$elements = $this->get('leadsfactory.leads_comments_repository')->getCommentsForLead($id);

		} else {
			return null;
		}

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:_fragment_comments_table.html.twig', array(  'leadId' => $id,
			'comments' => $elements));

	}

	/**
	 * Returns the leads list filtering form
	 *
	 * @return Form
	 */
	protected function getLeadsFilterForm()
	{
		$form = $this->createFormBuilder(array())
			->setMethod('GET')
			->setAction($this->generateUrl('_leads_list'))
			->add('form', 'choice', array(
					'choices'   => $this->getUserFormsOptions(),
					'label'     => 'Formulaire',
					'required'  => false
				)
			)
			->add('firstname', 'text', array('label' => 'Prénom', 'required' => false))
			->add('lastname', 'text', array('label' => 'Nom', 'required' => false))
			->add('email', 'text', array('label' => 'E-mail', 'required' => false))
			->add('datemin', 'date', array('label' => 'Date de début', 'widget'=>'single_text', 'required' => false))
			->add('datemax', 'date', array('label' => 'Date de fin', 'widget'=>'single_text', 'required' => false))
			->add('keyword', 'text', array('label' => 'Mot-clé', 'required' => false))
			->add('valider', 'submit', array('label' => 'Valider'))
		    ->getForm();

		return $form;
	}

	/**
	 * Returns the user's scope forms list options
	 *
	 * @return array
	 */
	protected function getUserFormsOptions()
	{
		$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->getUserForms($this->getUser());
		$options = array('' => 'Sélectionnez un formulaire');
		foreach($forms as $form){
			$options[$form->getId()] = $form->getName();
		}
		return $options;
	}

	/**
	 * Builds the report form
	 *
	 * @param array $filterParams
	 *
	 * @return Form
	 */
	protected function getReportForm($filterParams)
	{
		$export_formats = array('raw_csv' => 'CSV brut');

		//Le format "CSV amélioré" est dispo uniquement si un formulaire est sélectionné
		if(!empty($filterParams['form'])){
			$export_formats['nice_csv'] = 'CSV amélioré';
		}

		$form = $this->createFormBuilder(array())
			->setMethod('GET')
		    ->setAction($this->generateUrl('_leads_report'))
		    ->add('format', 'choice', array(
	            	'choices' => $export_formats,
	            	'label' => 'Format',
	        	)
	    	)
			->add('filterparams', 'hidden', array('data' => json_encode($filterParams)))
			->add('valider', 'submit', array('label' => 'Valider'))
	        ->getForm();

		return $form;
	}

	/**
	 * Generates the report
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/report", name="_leads_report")
	 */
	public function reportAction(Request $request)
	{
		$params = $request->query->get('form');
		$filterParams = json_decode($params['filterparams'], true);
		$format = $params['format'];

		$reportMethod = 'generate'.ucfirst($format);
		 return $this->$reportMethod($filterParams);
	}

	/**
	 * Generates raw CSV report
	 *
	 * @param array $filterParams
	 *
	 * @return Response
	 */
	public function generateRaw_csv($filterParams)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getIterableList($filterParams);

		$handle = fopen('php://memory', 'w');

		fputcsv($handle, array('id', 'Form', 'Date', 'Firstname', 'LastName', 'Email', 'Phone', 'Content'), ';');

		while (false !== ($row = $leads->next())) {
			fputcsv($handle, array(
					$row[0]->getId(),
					$row[0]->getForm()->getName(),
					$row[0]->getCreatedAt()->format('Y-m-d H:i:s'),
					$row[0]->getFirstname(),
					$row[0]->getLastname(),
					$row[0]->getEmail(),
					$row[0]->getTelephone(),
					$row[0]->getData()
				),
				';');

			$em->detach($row[0]);
		}

		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		$response =  new Response($content);
		$response->headers->set('content-type', 'text/csv');
		$response->headers->set('Content-Disposition', 'attachment; filename=leads_report.csv');

		return $response;
	}

	/**
	 * Generates nice CSV report
	 *
	 * @param array $filterParams
	 *
	 * @return Response
	 */
	public function generateNice_csv($filterParams)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$formUtils = $this->get('form_utils');

		$fields = $formUtils->getFieldsAsArrayByFormId($filterParams['form']);
		$columns = array_merge(array('id', 'Form', 'Date'), array_keys($fields));

		$leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getIterableList($filterParams);

		$handle = fopen('php://memory', 'w');
		fputcsv($handle, $columns, ';');
		while (false !== ($record = $leads->next())) {
			$row = array(
				$record[0]->getId(),
				$record[0]->getForm()->getName(),
				$record[0]->getCreatedAt()->format('Y-m-d H:i:s')
			);

			$data = json_decode($record[0]->getData(), true);

			foreach(array_keys($fields) as $field){
				$row[] = $formUtils->getFieldFrontendValue($fields[$field], $data[$field]);
			}

			fputcsv($handle, $row, ';');
			$em->detach($record[0]);
		}

		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		$response =  new Response($content);
		$response->headers->set('content-type', 'text/csv');
		$response->headers->set('Content-Disposition', 'attachment; filename=leads_report.csv');

		return $response;
	}

	private function sendNotificationEmail ( $action, $detailAction, Users $user, $message, $urlLead, $urlApplication, $scopeId ) {

		$toEmail = $user->getEmail();
		$toName = ucfirst($user->getFirstname()) . ' ' . ucfirst($user->getLastname());

		$to = array($toEmail => $toName);

		$prefUtils = $this->get('preferences_utils');
		$from = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_EMAIL_SENDER', $scopeId);

		$subject = "Lead's Factory : ".$action;

		$template = $this->renderView(
			'TellawLeadsFactoryBundle::emails/lead_notification.html.twig',
			array(
				"action" => $action,
				"detailAction" => $detailAction,
				"user" => $user,
				"message" => $message,
				"urlLead" => $urlLead,
				"urlApplication" => $urlApplication,
			)
		);

		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom($from)
			->setTo($to)
			->setBody($template, 'text/html');

		return $this->get('mailer')->send($message);

	}

}

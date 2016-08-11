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
use Tellaw\LeadsFactoryBundle\Utils\EmailUtils;
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

		PreferencesUtils::registerKey( "CORE_LEADSFACTORY_DISPATCH_EMAIL",
			"Email of the dispatch user",
			PreferencesUtils::$_PRIORITY_REQUIRED
		);

        parent::__construct();

    }

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/dispatchList/{page}/{limit}/{keyword}", name="_leads_dispatchList")
	 */
	public function indexDispatchAction(Request $request, $page=1, $limit=25, $keyword='')
	{

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		// find the dispatch user for user's scope
		$prefUtils = $this->get('preferences_utils');

		// First load from preferences the dispatch email for the current scope
		if (  $this->getUser()->getScope() != null )
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL', $this->getUser()->getScope()->getId() );
		else
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL' );

		// Then load the user
		$user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail($dispatchUserEmail);

		if ($user == null) {
			throw new Exception ("Dispatch user not found related to KEY CORE_LEADSFACTORY_DISPATCH_EMAIL and EMAIL : ".$dispatchUserEmail);
		}

		$filterForm = $this->getLeadsFilterForm();
		$filterForm->handleRequest($request);

		$filterParams["user"] = $user;
		$filterParams["affectation"] =  $user;
		$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:dispatchList.html.twig',
			array(
				'elements'      => $list['collection'],
				'pagination'    => $list['pagination'],
				'limit_options' => $list['limit_options'],
				'export_form'   => $this->getReportForm($filterParams)->createView()
			)
		);
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

			$filterParams["affectation"] = "mylist";
			$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
		}else{
			$filterParams["affectation"] = "affectation";
			$filterParams["user"] =  $this->getUser();
			$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
		}

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:list.html.twig',
			array(
				'type'			=> 'mylist',
				'firstName'		=> $this->getUser()->getFirstName(),
				'lastName'		=> $this->getUser()->getLastName(),
				'elements'      => $list['collection'],
				'pagination'    => $list['pagination'],
				'limit_options' => $list['limit_options'],
				'filters_form'  => $filterForm->createView(),
				'export_form'   => $this->getReportForm($filterParams)->createView()
			)
		);
	}

    /**
	 *
	 * Page de listing toutes les Leads
	 *
     * @Secure(roles="ROLE_USER")
     * @Route("/leads/list/{page}/{limit}/{keyword}", name="_leads_list")
     */
    public function indexAction(Request $request, $page=1, $limit=25, $keyword='')
    {

		$filterParams = null;
		$usersRepository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users');

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $filterForm = $this->getLeadsFilterForm();
	    $filterForm->handleRequest($request);

	    if ($filterForm->isValid()) {
		    $filterParams = $filterForm->getData();
			$filterParams["user"] = $this->getUser();

		if ($name = explode(" ", $filterParams["affectation"]))
			$filterParams["user"] = $this->getUser();
			$affectationUser = $usersRepository->findOneBy(array("firstname"=>$name, "lastname"=>$name));
			$filterParams["affectation"] = $affectationUser;
			$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }else{
			$filterParams["user"] =  $this->getUser();
		    $list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Leads:list.html.twig',
            array(
				'type'			=> 'list',
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
	            'filters_form'  => $filterForm->createView(),
	            'export_form'   => $this->getReportForm($filterParams)->createView(),
            )
        );
    }


	/**
	 *
	 * Page de Suivi mode CRM des leads
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/fragment/myleads/{page}/{limit}/{keyword}", name="_leads_fragment_myleads")
	 */
	public function crmMyLeadsFragmentAction ( Request $request, $page=1, $limit=25, $keyword='' ) {

		$session = $request->getSession();
		$filterParams = $session->get ("filterParamsSuivi");

		$usersRepository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users');

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		// Chargement des leads pour le dispatch
		$prefUtils = $this->get('preferences_utils');

		// First load from preferences the dispatch email for the current scope
		if (  $this->getUser()->getScope() != null )
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL', $this->getUser()->getScope()->getId() );
		else
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL' );

		// Then load the user
		$user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail($dispatchUserEmail);

		if ($user == null) {
			throw new Exception ("Dispatch user not found related to KEY CORE_LEADSFACTORY_DISPATCH_EMAIL and EMAIL : ".$dispatchUserEmail);
		}


		if ($this->get('security.authorization_checker')->isGranted('ROLE_DISPATCH')) {
			$filterParams["user"] = $this->getUser();
			$filterParams["affectation"] = $user;
			$listDispatch = $this->getList('TellawLeadsFactoryBundle:Leads', 1, 1000, '', $filterParams);
		} else {
			$listDispatch= array("collection" => "");
		}

		// Chargement des Leads
		$filterParams["affectation"] = null;

		if ($filterParams["affectation"] == "") {
			$filterParams["affectation"] = $this->getuser();
		}

		$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:_fragment_myleads.html.twig',
			array(
				'type'			=> 'list',
				'user'			=> $this->getUser(),
				'affectation'	=> ucfirst($this->getuser()->getFirstName()). " ". ucfirst($this->getuser()->getLastName()),
				'dispatch'		=>  $listDispatch['collection'],
				'elements'      => $list['collection'],
				'pagination'    => $list['pagination'],
				'limit_options' => $list['limit_options'],
				'export_form'   => $this->getReportForm($filterParams)->createView(),
			)
		);


	}

	/**
	 *
	 * Page de Suivi mode CRM des leads
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/fragment/teamleads/{page}/{limit}/{keyword}", name="_leads_fragment_teamleads")
	 */
	public function crmTeamLeadsFragmentAction ( Request $request, $page=1, $limit=25, $keyword='' ) {

		$id = $request->get("id");

		$session = $request->getSession();
		$filterParams = $session->get ("filterParamsSuivi");

		$usersRepository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users');

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		// Checking if user is related to a team
		$json = null;
		if ($this->getUser()->getScope() != null) {
			$filePath = $this->get('kernel')->getRootDir()."/config/".$this->getUser()->getScope()->getCode()."-team-description.json";
			if (file_exists( $filePath )) {
				$jsonArray = json_decode(file_get_contents( $filePath ), true);
			}
		}

		$isManagerOfATeam = false;
		$teamName = "";
		$listTeam = array();
		if ( $jsonArray ) {

			if ( $this->getUser()->getEmail() != null && $this->getUser()->getEmail() != "" ) {
				if (array_key_exists($this->getUser()->getEmail(), $jsonArray )) {
					foreach ( $jsonArray[$this->getUser()->getEmail()] as $teamDetail ) {
						$isManagerOfATeam = true;
						$teamName =  $teamDetail["name"];
						$teamId =  $teamDetail["id"];
						$members =  $teamDetail["members"];
						if( $id == $teamId ) {
							$filterParams["affectation"] = $usersRepository->findBy(array("email"=>$members));
							$listTeam = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
							break;
						}
					}
				}
			}

		}


		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:_fragment_teamleads.html.twig',
			array(
				'type'			=> 'list',
				'objectId'			=> $id,
				'teamName'			=> $teamName,
				'isManagerOfATeam' 	=> $isManagerOfATeam,
				'listTeam'			=> $listTeam['collection'],
				'paginationTeam'    => $listTeam['pagination'],
				'limit_optionsTeam' => $listTeam['limit_options'],

				'user'			=> $this->getUser(),
			)
		);

	}

	/**
	 *
	 * Page de Suivi mode CRM des leads
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/fragment/filterParam", name="_leads_fragment_filterparam")
	 */
	public function crmFilterParamAction ( Request $request ) {

		$filterForm = $this->getLeadsFilterForm("_leads_suivi");
		$filterForm->handleRequest($request);
		$filterParams = $filterForm->getData();

		$session = $request->getSession();
		$session->set('filterParamsSuivi', $filterParams);

		return new Response("ok");

	}

	/**
	 *
	 * Page de Suivi mode CRM des leads
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/fragment/dptleads/{page}/{limit}/{keyword}", name="_leads_fragment_dptleads")
	 */
	public function crmDptLeadsFragmentAction ( Request $request, $page=1, $limit=25, $keyword='' ) {

		$id = $request->get("id");
		$usersRepository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users');

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		$session = $request->getSession();
		$filterParams = $session->get ("filterParamsSuivi");

		// Loading informations of departement
		$json = null;
		if ($this->getUser()->getScope() != null) {
			$filePath = $this->get('kernel')->getRootDir()."/config/".$this->getUser()->getScope()->getCode()."-dpt-description.json";
			if (file_exists( $filePath )) {
				$jsonArray = json_decode(file_get_contents( $filePath ), true);
			}
		}
		$isInADpt = false;
		$dptName = "";
		$dptTeam = array();
		if ( $jsonArray ) {

			if ( $this->getUser()->getEmail() != null && $this->getUser()->getEmail() != "" ) {

				foreach ( $jsonArray as $dpt ) {

					$members =  $dpt["members"];
					if (in_array( $this->getUser()->getEmail() , $members )) {
						$dptName =  $dpt["name"];
						$dptId =  $dpt["id"];
						$isInADpt = true;

						if ($dptId == $id) {
							$filterParams["affectation"] = $usersRepository->findBy(array("email"=>$members));
							$dptTeam = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);

							break;
						}
					}
				}
			}

		}

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:_fragment_dptleads.html.twig',
			array(
				'type'			=> 'list',
				'objectId'			=> $id,
				'dptName'			=> $dptName,
				'isInADpt' 			=> $isInADpt,
				'dptTeam'			=> $dptTeam['collection'],
				'paginationDpt'    	=> $dptTeam['pagination'],
				'limit_optionsDpt' 	=> $dptTeam['limit_options'],

				'user'			=> $this->getUser(),
			)
		);

	}

	/**
	 *
	 * Page de Suivi mode CRM des leads
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/suivi/{page}/{limit}/{keyword}", name="_leads_suivi")
	 */
	public function crmAction (Request $request, $page=1, $limit=25, $keyword='') {

		$filterParams = null;
		$usersRepository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users');

		if ($this->get("core_manager")->isDomainAccepted ()) {
			return $this->redirect($this->generateUrl('_security_licence_error'));
		}

		// Chargement des leads pour le dispatch
		$prefUtils = $this->get('preferences_utils');

		// First load from preferences the dispatch email for the current scope
		if (  $this->getUser()->getScope() != null )
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL', $this->getUser()->getScope()->getId() );
		else
			$dispatchUserEmail = $prefUtils->getUserPreferenceByKey ('CORE_LEADSFACTORY_DISPATCH_EMAIL' );

		// Then load the user
		$user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail($dispatchUserEmail);

		if ($user == null) {
			throw new Exception ("Dispatch user not found related to KEY CORE_LEADSFACTORY_DISPATCH_EMAIL and EMAIL : ".$dispatchUserEmail);
		}

		$filterForm = $this->getLeadsFilterForm("_leads_suivi");
		$filterForm->handleRequest($request);

		if ($this->get('security.authorization_checker')->isGranted('ROLE_DISPATCH')) {
			$filterParams["user"] = $this->getUser();
			$filterParams["affectation"] = $user;
			$listDispatch = $this->getList('TellawLeadsFactoryBundle:Leads', 1, 1000, '', $filterParams);
		} else {
			$listDispatch= array("collection" => "");
		}

		// Chargement des Leads
		$filterParams["affectation"] = null;
		if ($filterForm->isValid()) {
			$filterParams = $filterForm->getData();

			// Si le formulaire à été validé, nous remplissons le champ de recherche Ajax avec la bonne valeur.
			if ($name = explode(" ", $filterParams["affectation"])) {
				$affectationUser = $usersRepository->findOneBy(array("firstname"=>$name, "lastname"=>$name));
				$filterParams["affectation"] = $affectationUser;
			}
		}

		// Si le champs affectation est vide, nous le remplissons avec l'utilisateur actuel.
		if ($filterParams["affectation"] == "") {

			// FilterParam ne sert que pour la recherche.
			$filterParams["affectation"] = $this->getuser();

			// Nous remplissons egallement le champs pour le formulaire.
			if (!$filterForm->isSubmitted()) {
				$filterForm->get("affectation")->setData(ucfirst($this->getUser()->getFirstName())." ".ucfirst($this->getUser()->getlastName()));
			}

		}

		$list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);

		$json = null;
		// Checking if user is related to a team
		if ($this->getUser()->getScope() != null) {
			$filePath = $this->get('kernel')->getRootDir()."/config/".$this->getUser()->getScope()->getCode()."-team-description.json";
			if (file_exists( $filePath )) {
				$jsonArray = json_decode(file_get_contents( $filePath ), true);
			}
		}

		$isManagerOfATeam = false;
		$teams = array();
		$teamName = "";

		if ( $jsonArray ) {
			if ($this->getUser()->getEmail() != null && $this->getUser()->getEmail() != "") {
				if (array_key_exists($this->getUser()->getEmail(), $jsonArray)) {
					foreach ($jsonArray[$this->getUser()->getEmail()] as $teamDetail) {

						$isManagerOfATeam = true;
						$teamName = $teamDetail["name"];
						$teamId = $teamDetail["id"];

						$teams[$teamName] = $teamId;

					}
				}
			}
		}

		// Loading informations of departement
		$json = null;
		if ($this->getUser()->getScope() != null) {
			$filePath = $this->get('kernel')->getRootDir()."/config/".$this->getUser()->getScope()->getCode()."-dpt-description.json";
			if (file_exists( $filePath )) {
				$jsonArray = json_decode(file_get_contents( $filePath ), true);
			}
		}

		$isInADpt = false;
		$dptName = "";
		$departements = array();
		if ( $jsonArray ) {

			if ( $this->getUser()->getEmail() != null && $this->getUser()->getEmail() != "" ) {
				foreach ( $jsonArray as $dpt ) {
					$members =  $dpt["members"];
					if (in_array( $this->getUser()->getEmail() , $members )) {

						$dptName =  $dpt["name"];
						$dptId =  $dpt["id"];
						$isInADpt = true;

						$departements [ $dptName ] = $dptId;

					}
				}
			}

		}

		return $this->render(
			'TellawLeadsFactoryBundle:entity/Leads:suivi.html.twig',
			array(
				'type'			=> 'list',

				'dptName'			=> $dptName,
				'isInADpt' 			=> $isInADpt,
				'teams'				=> $teams,

				'teamName'			=> $teamName,
				'isManagerOfATeam' 	=> $isManagerOfATeam,
				'departements'		=> $departements,

				'user'			=> $this->getUser(),
				'affectation'	=> $filterForm->get("affectation")->getData(),
				'dispatch'		=>  $listDispatch['collection'],
				'elements'      => $list['collection'],
				'pagination'    => $list['pagination'],
				'limit_options' => $list['limit_options'],
				'filters_form'  => $filterForm->createView(),
				'export_form'   => $this->getReportForm($filterParams)->createView(),
			)
		);

	}

	/**
	 * @Route("/leads/suivi-edit/{id}/{origin}", name="_leads_suivi_edit")
	 * @Secure(roles="ROLE_USER")
	 * @Template()
	 */
	public function suiviEditAction( Request $request, $id, $origin = 0 )
	{

		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);


		$printMode = $request->query->get("printMode");

		if (is_null($printMode))
			$printMode = false;
		else
			$printMode = true;



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


		$file = "";
		if ( array_key_exists( "user_file", $leadDetail  )) {
			$ext = substr(strrchr($leadDetail["user_file"], '.'), 1);
			$filePath = $this->get('kernel')->getRootDir()."/../datas/".$lead->getForm()->getid()."/".$id."_user_file.".$ext;
			if (file_exists( $filePath )) {
				$file = $lead->getForm()->getId()."/".$id."_user_file.".$ext;
			}
		}

		if (!$printMode) {
			$page = "suivi-edit";
		} else {
			$page = "suivi-edit-print";
		}

		return $this->render('TellawLeadsFactoryBundle:entity/Leads:'.$page.'.html.twig', array(  'lead' => $lead,
			'origin' => $origin,
			'leadDetail' => $leadDetail,
			'assignUser' => $assignUser,
			"file"	=> $file,
			'title' => "Edition d'un leads"));
	}

    /**
     * @Route("/leads/edit/{id}/{origin}", name="_leads_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id, $origin = 0 )
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

		$file = "";
		if ( array_key_exists( "user_file", $leadDetail  )) {
			$ext = substr(strrchr($leadDetail["user_file"], '.'), 1);
			$filePath = $this->get('kernel')->getRootDir()."/../datas/".$lead->getForm()->getid()."/".$id."_user_file.".$ext;
			if (file_exists( $filePath )) {
				$file = $lead->getForm()->getId()."/".$id."_user_file.".$ext;
			}
		}


        return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit.html.twig', array( "lead" => $lead,
																							"origin" => $origin,
																							"leadDetail" => $leadDetail,
																							"assignUser" => $assignUser,
																							"file"	=> $file,
                                                                                            "title" => "Edition d'un leads"));
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

		// Index leads on search engine
		$leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leadId);
		$this->get('search.utils')->indexLeadObject($leads_array, $lead->getForm()->getScope()->getCode());

		return new Response('ok');

	}

	/**
	 * @Route("/leads/json/update", name="_leads_json_update_field")
	 * @Secure(roles="ROLE_USER")
	 */
	public function updateJsonFieldToLead( Request $request ) {

		$leadId = $request->request->get ("leadId");
		$leadField = $request->request->get ("leadField");
		$leadFieldValue = $request->request->get ("leadFieldValue");

		$lead = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($leadId);

		$leadDetail = json_decode($lead->getData(), true);
		$leadDetail[$leadField] = $leadFieldValue;

		$lead->setData ( json_encode($leadDetail) );

		$em = $this->getDoctrine()->getManager();
		$em->persist($lead);
		$em->flush();

		// Index leads on search engine
		$leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leadId);
		$this->get('search.utils')->indexLeadObject($leads_array, $lead->getForm()->getScope()->getCode());

		return new Response($leadFieldValue);

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
		/* @var EmailUtils $emailUtils */
		$emailUtils = $this->get("emails_utils");

		$action 		= sprintf(EmailUtils::$_ACTION_TYPE_LEADS,$leadId);
		$detailedAction = EmailUtils::$_DETAILED_ACTION_TYPE_LEADS;
		$message 		= sprintf(EmailUtils::$_MESSAGE_TYPE_LEADS, array( 	date ("d/m/Y à h:i"),
			ucfirst($this->getUser()->getFirstName()),
			ucfirst($this->getUser()->getLastName()),
			$leadId)) ;

		$result = $emailUtils->sendUserNotification (
			$this->getUser(),
			$action,
			$detailedAction,
			$message,
			$leadsUrl,
			$leadsUrl
		);

		
		// Index leads on search engine
		$leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leadId);
		$this->get('search.utils')->indexLeadObject($leads_array, $lead->getForm()->getScope()->getCode());

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

		/* @var EmailUtils $emailUtils */
		$emailUtils = $this->get("emails_utils");

		$action 		= sprintf(EmailUtils::$_ACTION_THEME_LEADS,$leadId);
		$detailedAction = EmailUtils::$_DETAILED_ACTION_THEME_LEADS;
		$message 		= sprintf(EmailUtils::$_MESSAGE_THEME_LEADS, array( 	date ("d/m/Y à h:i"),
																				ucfirst($this->getUser()->getFirstName()),
																				ucfirst($this->getUser()->getLastName()),
																				$leadId)) ;

		$result = $emailUtils->sendUserNotification (
			$this->getUser(),
			$action,
			$detailedAction,
			$message,
			$leadsUrl,
			$leadsUrl
		);

		// Index leads on search engine
		$leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leadId);
		$this->get('search.utils')->indexLeadObject($leads_array, $lead->getForm()->getScope()->getCode());

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
		$users = array();

		if ($scope = $this->getUser()->getScope())
			$users = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->getList(1, 10, $term, array('scope'=>$scope->getId()) );

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

		/* @var EmailUtils $emailUtils */
		$emailUtils = $this->get("emails_utils");

		$action 		= sprintf(EmailUtils::$_ACTION_AFFECT_LEADS,$leadId);
		$detailedAction = EmailUtils::$_DETAILED_ACTION_AFFECT_LEADS;
		$message 		= sprintf(EmailUtils::$_MESSAGE_AFFECT_LEADS, array( 	date ("d/m/Y à h:i"),
																				ucfirst($this->getUser()->getFirstName()),
																				ucfirst($this->getUser()->getLastName()),
																				$leadId)) ;

		$emailUtils->sendUserNotification (
			$user,
			$action,
			$detailedAction,
			$message,
			$leadsUrl,
			$leadsUrl
		);

		// Index leads on search engine
		$leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leadId);
		$this->get('search.utils')->indexLeadObject($leads_array, $lead->getForm()->getScope()->getCode());

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
	protected function getLeadsFilterForm( $controller = "_leads_list" )
	{
		$form = $this->createFormBuilder(array(),array( 'attr' => ['id' => 'filterform']))
			->setMethod('GET')
			->setAction($this->generateUrl($controller))
			->add('form', 'choice', array(
					'choices'   => $this->getUserFormsOptions(),
					'label'     => 'Formulaire',
					'required'  => false
				)
			)
			->add('firstname', 'text', array('attr'=>array('class'=>'long'), 'label' => 'Prénom', 'required' => false))
			->add('lastname', 'text', array('label' => 'Nom', 'required' => false))
			->add('email', 'text', array('label' => 'E-mail', 'required' => false))
			->add('utmcampaign', 'text', array('label' => 'Code Action', 'required' => false))
			->add('datemin', 'date', array('label' => 'Date de début', 'widget'=>'single_text', 'required' => false))
			->add('datemax', 'date', array('label' => 'Date de fin', 'widget'=>'single_text', 'required' => false))
			->add('keyword', 'text', array('label' => 'Mot-clé', 'required' => false))
			->add('affectationId', 'hidden', array('label' => '', 'required' => false))
			->add('affectation', 'text', array('label' => 'Affectation', 'required' => false))
			->add('workflowStatus', 'choice', array('choices' => $this->getLeadsWorkflowOptions("status"), 'label' => 'Statut', 'required' => false))
			->add('workflowType', 'choice', array('choices' => $this->getLeadsWorkflowOptions("type"), 'label' => 'Type', 'required' => false))
			->add('workflowTheme', 'choice', array('choices' => $this->getLeadsWorkflowOptions("theme"), 'label' => 'Thème', 'required' => false))
			->add('valider', 'submit', array('label' => 'Valider'))
		    ->getForm();

		return $form;
	}


	/**
	 * @param null $target
	 * @return array|null
     */
	protected function getLeadsWorkflowOptions($target=null) {

		if ($target == null) {
			return null;
		}

		$listCode = "leads-".$target;
		if ( $this->getUser()->getScope() == null ) {
			return null;
		}
		$scopeId = $this->getUser()->getScope()->getId();
		$dataDictionnary = $this->get("leadsfactory.datadictionnary_repository");
		$dataDictionnaryId = $this->get("leadsfactory.datadictionnary_repository")->findByCodeAndScope( $listCode, $scopeId );
		$elements = $dataDictionnary->getElementsByOrder( $dataDictionnaryId, "rank", "ASC" );
		if (count($elements) > 2)
			$options = array('' => 'Sélectionnez');
		else
			$options = array('' => 'Pas de données');
		foreach ($elements as $element) {
			$options[$element->getValue()] = $element->getName();
		}
		return $options;
	}

	/**
	 * Returns the user's scope forms list options
	 *
	 * @return array
	 */
	protected function getUserFormsOptions()
	{
		$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->getForms();
		$options = array('' => 'Sélectionnez un formulaire');
		$user_scope = $this->get('security.context')->getToken()->getUser()->getScope();
		foreach($forms as $form){
			if ($user_scope && $form->getscope() != $user_scope) continue;
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

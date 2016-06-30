<?php

namespace Weka\LeadsExportBundle\Controller\Admin;

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



/**
 * @Route("/project")
 */
class AdminFormController extends CoreController
{

    public function __construct () {

        parent::__construct();

    }

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/saisie/form/{code}", name="_project_form_display")
	 */
	public function indexDispatchAction(Request $request, $code)
	{

		return $this->render(
			'WekaLeadsExportBundle:Custom:pageForForms.html.twig',
			array(
				"code" => $code,
				"userEmail" => ""
			)
		);
	}

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/saisie/formForceUser/{code}", name="_project_formForceUser_display")
	 */
	public function indexForceUserAction(Request $request, $code)
	{

		return $this->render(
			'WekaLeadsExportBundle:Custom:pageForForms.html.twig',
			array(
				"code" => $code,
				"userEmail" => $this->getUser()->getEmail()
			)
		);
	}

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/dashboard/manager/{pageId}", name="_project_dashboard_manager")
	 */
	public function managerDashboardAction ( $pageId ) {

		// Loading informations of departement
		$filePath = $this->get('kernel')->getRootDir()."/config/dashboard-".$pageId.".json";
		if (file_exists( $filePath )) {
			$jsonArray = json_decode(file_get_contents( $filePath ), true);
		}

		return $this->render('WekaLeadsExportBundle:Default:dashboard-manager.html.twig', array( "configuration" => $jsonArray ));

	}

	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/dashboard/widget", name="_project_dashboard_widget")
	 */
	public function graphWidgetAction (Request $request) {

		// Get parameters
		$id = $request->request->get("id");

		$filePath = $this->get('kernel')->getRootDir()."/config/dashboard-widgets.json";
		if (file_exists( $filePath )) {
			$jsonArray = json_decode(file_get_contents( $filePath ), true);
		} else {
			throw new \Exception ("Widget configuration file doesn't exists");
		}

		if (!array_key_exists($id,$jsonArray )) {
			return new Response("");
		}

		$dataProviderClass = $jsonArray[$id]["dataProvider"];
		//$dataProviderClass = "Weka\LeadsExportBundle\Utils\DataProviders\DemoDataProviders";
		$renderProviderView = $jsonArray[$id]["renderProvider"];

		// Load a widget
		$dataProvider = new $dataProviderClass();
		$dataProvider->setSearchUtils ( $this->get("search.utils") );
		$dataProvider->setContainer ( $this->container );

		// Output
		return $this->render('WekaLeadsExportBundle:'.$renderProviderView.'.html.twig', array(
			"id" => $id,
			"widget"=>$jsonArray[$id],
			"data" => $dataProvider->getDatas($jsonArray[$id]["renderArgument"]),
			"renderArgument" => $jsonArray[$id]["renderArgument"]
		));
	}

}

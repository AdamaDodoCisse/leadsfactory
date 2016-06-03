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
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;
use Swift_Message;

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
				"code" => $code
			)
		);
	}
}

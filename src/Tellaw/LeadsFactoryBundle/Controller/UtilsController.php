<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/utils")
 */
class UtilsController extends AbstractLeadsController
{

    /**
     *
     * @Route("/navigation/{parentRoute}", name="_utils_navigation")
     * @Secure(roles="ROLE_USER")
     * @template()
     */
    public function navigationAction(Request $request, $parentRoute)
    {

        $sections = array(  "formulaires" => '0', "donnees" => '0', "users" => 0 );

        $mainRoute = $parentRoute;

        if (    substr ($mainRoute, 0, strlen ("_form_")) == "_form_"   ||
                substr ($mainRoute, 0, strlen ("_formType_")) == "_formType_" ||
                substr ($mainRoute, 0, strlen ("_referenceList_")) == "_referenceList_"   ) {

            $sections['formulaires'] = '1';

        } else if (    substr ($mainRoute, 0, strlen ("_leads_")) == "_leads_"  ) {

            $sections['donnees'] = '1';

        } else if (    substr ($mainRoute, 0, strlen ("_users_")) == "_users_"  ) {

            $sections['users'] = '1';

        }

        return $this->render($this->getBaseTheme().':Utils:navigation.html.twig', array ("sections" => $sections, "route" => $mainRoute));

    }

}

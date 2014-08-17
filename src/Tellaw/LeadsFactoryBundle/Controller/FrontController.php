<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Utils\FormUtils;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/client")
 * @Cache(expires="tomorrow")
 */
class FrontController extends Controller
{

    /**
     *
     * @Route("/form/{id}", name="_client_get_form")
     *
     */
    public function getFormAction(Request $request, $id )
    {

        //$formUtils = new FormUtils();
        $formUtils = $this->get("form_utils");

        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);

        $source = $object->getSource();

        //$tags = $formUtils->parseTags( $source );

        $html = $formUtils->buildHtmlForm( $source, $id );

        echo ("Success<br/>");
        echo ($html);
        die();


    }

    /**
     *
     * Method used to process actions.
     * 1) Log in file first request
     * 2) Save in DB
     *
     * @Route("/post", name="_client_post_form")
     *
     * @param Request $request
     * @param $id
     */
    public function postLeadsAction ( Request $request ) {

        $fields = $request->get ("lffield");
        $json = json_encode( $fields );
        $redirectUrlSuccess = $fields["successUrl"];
        $redirectUrlError = $fields["errorUrl"];

        // Create new Leads Entity Objects
        $leads = new Leads();
        $leads->setFirstname( $fields["firstname"] );
        $leads->setLastname( $fields["lastname"] );
        $leads->setData( $json );
        $leads->setLog( "leads importée le : ".date('Y-m-d h:s') );
        $leads->setUtmcampaign( $fields["utmcampaign"] );
        $leads->setTelephone( $fields["phone"] );
        $leads->setCreatedAt( new \DateTime() );

        if ( trim ( $redirectUrlSuccess ) != "") {
            return $this->redirect($redirectUrlSuccess);
        }

        die();
    }

}
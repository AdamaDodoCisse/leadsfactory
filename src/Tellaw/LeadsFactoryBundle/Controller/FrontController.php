<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/client")
 */
class FrontController extends Admin\AbstractLeadsController
{

    /**
     * @Route("/form/{id}", name="_client_get_form")
     */
    public function getFormAction(Request $request, $id )
    {

        //$formUtils = new FormUtils();
        $formUtils = $this->get("form_utils");

        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);

        $source = $object->getSource();

        //$tags = $formUtils->parseTags( $source );

        $html = $formUtils->buildHtmlForm( $source, $id, $object );

        return $this->render(
            $this->getBaseTheme().':Front:display_form.html.twig',
            array(  'formHtmlObject' => $html )
        );

    }

    /**
     * @Route("/form/js/{code}", name="_client_get_form_js")
     */
    public function getFormAsJsAction ( Request $request, $code ) {

        //$formUtils = new FormUtils();
        /** @var \Tellaw\LeadsFactoryBundle\Utils\JsUtils $formUtils */
        $formUtils = $this->get("js_utils");

        /** @var \Tellaw\LeadsFactoryBundle\Entity\Form $object */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($code);

        $source = $object->getSource();

        //$tags = $formUtils->parseTags( $source );

        $jsForm = $formUtils->buildAndWrapForm ( $source, $object->getId(), $object );

        echo ($jsForm);
        die();

    }

    /**
     *
     * Method used to process actions.
     * 1) Log in file first request
     * 2) Save in DB
     *
     * @Route("/post", name="_client_post_form")
     * @param Request $request
     * @param $id
     */
    public function postLeadsAction ( Request $request ) {

        $formUtils = $this->get("form_utils");

        $fields = $request->get ("lffield");
        $json = json_encode( $fields );
        $redirectUrlSuccess = (string)$request->get ("successUrl");
        $redirectUrlError = (string)$request->get ("errorUrl");

        $exportUtils = $this->get('export_utils');

        if ( !$formUtils->checkFormKey( $request->get("lfFormKey"), $request->get("lfFormId") ) )
            throw new \Exception ("Form Key is not allowed");

        try {

            $formTypeObject = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find((string)$request->get ("lfFormType"));
            $formObject = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($request->get("lfFormId"));

            // Read configuration to map attributes correctly
            $decodedJson = $formObject->getConfig();
//print_r ($json);die();

            if ( array_key_exists('configuration', $decodedJson) ) {

                if (array_key_exists(  'lastname' , $decodedJson["configuration"] )) {
                    $fields["lastname"] = ucfirst ($fields[ $decodedJson["configuration"]["lastname"] ]);
                }

                if (array_key_exists(  'firstname' , $decodedJson["configuration"] )) {
                    $fields["firstname"] = ucfirst( $fields[ $decodedJson["configuration"]["firstname"] ] );
                }
            }

            // Create new Leads Entity Object
            $leads = new Leads();
            $leads->setFirstname( @$fields["firstname"] );
            $leads->setLastname( @$fields["lastname"] );
            $leads->setData( $json );
            $leads->setLog( "leads importée le : ".date('Y-m-d h:s') );
            $leads->setUtmcampaign( @$fields["utmcampaign"] );
            $leads->setFormType( $formTypeObject );
            $leads->setForm($formObject);
            $leads->setTelephone( @$fields["phone"] );



            $status = $exportUtils->hasScheduledExport($formObject->getConfig()) ? $exportUtils::$_EXPORT_NOT_PROCESSED : $exportUtils::$_EXPORT_NOT_SCHEDULED;
            $leads->setStatus($status);

            $leads->setCreatedAt( new \DateTime() );

            $em = $this->getDoctrine()->getManager();
            $em->persist($leads);
            $em->flush();

            // Create export job(s)
            if($status == $exportUtils::$_EXPORT_NOT_PROCESSED){
                $exportUtils->createJob($leads);
            }

            if ( trim ( $redirectUrlSuccess ) != "") {
                return $this->redirect($redirectUrlSuccess);
            }

        } catch (Exception $e) {
            return $this->redirect($redirectUrlError);
        }

        echo ("Done");
        die();
    }

    /**
     * @Route("/form/ajax/list_options", name="_ajax_child_list_options")
     */
    public function getChildListOptionsAction(Request $request)
    {
        $parentCode = $request->query->get('parent_code');
        $parentValue = $request->query->get('parent_value');
        $default  = $request->query->get('default');

        if(empty($parentValue)){
            $optionsHtml = '<option value="">'.$default.'</option>';

        }else{
            $parentList = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findByCode($parentCode);
            $parentItem = $this->getdoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->findOneBy(array(
                'value'         => $parentValue,
                'referenceList' => $parentList
            ));

            $children = (!empty($parentItem)) ? $parentItem->getChildren()->getValues() : array();

            $optionsHtml = '<option value="">Sélectionnez</option>';
            foreach($children as $child){
                $optionsHtml .= '<option value="'.$child->getValue().'">'.$child->getName().'</option>';
            }
        }

        return new Response($optionsHtml);
    }

}
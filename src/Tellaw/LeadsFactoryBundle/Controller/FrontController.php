<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
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
use Swift_Message;

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

        $html = $formUtils->buildHtmlForm( $object );

        return $this->render(
            $this->getBaseTheme().':Front:display_form.html.twig',
            array(  'formHtmlObject' => $html )
        );

    }

    /**
     * @Route("/form/js/{code}", name="_client_get_form_js")
     */
    public function getFormAsJsAction($code)
    {
        /** @var \Tellaw\LeadsFactoryBundle\Utils\JsUtils $formUtils */
        $formUtils = $this->get("js_utils");

        /** @var \Tellaw\LeadsFactoryBundle\Entity\Form $form */
        $form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($code);
        $jsForm = $formUtils->buildAndWrapForm ($form);

        return new Response($jsForm);
    }

    /**
     * Method used to process actions.
     * 1) Log in file first request
     * 2) Save in DB
     *
     * @Route("/post", name="_client_post_form")
     * @param Request $request
     *
     * @throws \Exception
     * @return Response
     */
    public function postLeadsAction ( Request $request ) {

        $logger = $this->get('logger');

        $formUtils = $this->get("form_utils");

        $fields = $request->get ("lffield");
        $json = json_encode( $fields );

        $exportUtils = $this->get('export_utils');

        if ( !$formUtils->checkFormKey( $request->get("lfFormKey"), $request->get("lfFormId") ) )
            throw new \Exception ("Form Key is not allowed");

        try {

            $formTypeObject = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find((string)$request->get ("lfFormType"));
            $formObject = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($request->get("lfFormId"));

            // Read configuration to map attributes correctly
            $config = $formObject->getConfig();

            $redirectUrlSuccess = isset($config['redirect']['url_success']) ? $config['redirect']['url_success'] : '';
            $redirectUrlError = isset($config['redirect']['url_error']) ? $config['redirect']['url_error'] : '';

            if ( array_key_exists('configuration', $config) ) {

                if (array_key_exists(  'lastname' , $config["configuration"] )) {
                    $fields["lastname"] = ucfirst ($fields[ $config["configuration"]["lastname"] ]);
                }

                if (array_key_exists(  'firstname' , $config["configuration"] )) {
                    $fields["firstname"] = ucfirst( $fields[ $config["configuration"]["firstname"] ] );
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

            //Send notification
            if(isset($config['notification'])){
                $this->sendNotification($config['notification'], $leads);
            }

            //Send confirmation email
            if(isset($config['confirmation_email'])){
                $this->sendConfirmationEmail($config['confirmation_email'], $leads);
            }

            //Redirect to success page
            if (!empty($redirectUrlSuccess)) {
                return $this->redirect($redirectUrlSuccess);
            }

        } catch (Exception $e) {
            $logger->error('postLeadsAction Error ');
            return $this->redirect($redirectUrlError);
        }

        return new Response('Done');
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

    /**
     * Send email notification
     *
     * @param array $params
     * @param \Tellaw\LeadsFactoryBundle\Entity\Leads $leads
     */
    protected function sendNotification($params, $leads)
    {
        $logger = $this->get('logger');
        $exportUtils = $this->get('export_utils');

        $data = json_decode($leads->getData(), true);

        if(!isset($params['to'])){
            $logger->error('No recipient available, check JSON form config');
            return;
        }

        $to = $params['to'];
        $from = isset($params['from']) ? $params['from'] : $exportUtils::NOTIFICATION_DEFAULT_FROM;
        $subject = isset($params['subject']) ? $params['subject'] : 'Nouvelle DI issue du formulaire '.$leads->getForm()->getName();
        $template = isset($params['template']) ? $params['template'] : $exportUtils::NOTIFICATION_DEFAULT_TEMPLATE;

        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($this->renderView($this->getBaseTheme().':'.$template,
                array(
                    'fields' => $data,
                    'intro' => 'Nouvelle DI issue du formulaire '.$leads->getForm()->getName())), 'text/html'
            );

        try{
            $result = $this->get('mailer')->send($message);
        }catch(Exception $e){
            $logger->error($e->getMessage());
        }
    }

    /**
     * Send confirmation email
     *
     * @param array $params
     * @param $leads
     */
    protected function sendConfirmationEmail($params, $leads)
    {
        $logger = $this->get('logger');

        $form = $leads->getForm();

        if(empty($params['to']['email_input_id']) || empty($params['to']['firstname_input_id']) || empty($params['to']['lastname_input_id'])){
            $logger->error('bad confirmation email configuration (form '.$form->getName().')');
            return;
        }

        $data = json_decode($leads->getData(), true);

        $toEmail = $data[$params['to']['email_input_id']];
        $toName = $data[$params['to']['firstname_input_id']] . ' ' . $data[$params['to']['lastname_input_id']];

        $to = array($toEmail => $toName);
        $from = $params['from'];
        $subject = $this->renderTemplate($params['subject'], $data);

        $template = $form->getConfirmationEmailSource();
        $body = $this->renderTemplate($template, $data);

        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, 'text/html');

        try{
            $result = $this->get('mailer')->send($message);
        }catch(Exception $e){
            $logger->error($e->getMessage());
        }
    }

    /**
     * Render template variables {{ var }}
     *
     * @param $str
     * @param $data
     * @return mixed
     */
    protected function renderTemplate($str, $data)
    {
        $hasVars = preg_match_all('/{{[\s]*([^\s{}]*)[\s]*}}/', $str, $matches);

        if(!$hasVars)
            return $str;

        $replacement = array();
        foreach($matches[1] as $key){
            $replacement[] = isset($data[$key]) ? $data[$key] : '';
        }

        $str = str_replace($matches[0], $replacement, $str);

        return $str;
    }

}
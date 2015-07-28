<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Tracking;
use Tellaw\LeadsFactoryBundle\Response\TransparentPixelResponse;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
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
 *
 */
class FrontController extends CoreController
{

    /**
     * Tracking callback method
     * @Route("/form/trck", name="_client_form_tracking")
     * @Route("/form/trck/{code}/")
     * @Route("/form/trck/{code}/{utm_campaign}")
     */
    public function trackingAction( Form $form, $utm_campaign = '' ){

        // Track call request
        /** @var \Tellaw\LeadsFactoryBundle\Entity\Tracking $tracking */
        $tracking = new Tracking();
        if (trim($utm_campaign) == '') {
            $utm_campaign =  $form->getUtmcampaign();
        }

        $tracking->setUtmCampaign( $utm_campaign );
        $tracking->setForm( $form );
        $tracking->setCreatedAt( new \DateTime() );

        $em = $this->getDoctrine()->getManager();
        $em->persist($tracking);
        $em->flush();

        return new TransparentPixelResponse();

    }


    /**
     * @Route("/form/twig/{code}/{utm_campaign}", name="_client_twig")
     * @ParamConverter("form")
     */
    public function twigAction(Form $form, $utm_campaign = '')
	{
		$post_url = $this->get('router')->generate('_client_post_form', array(), true);
		$hidden_tags = $this->get('form_utils')->getHiddenTags($form);

        $cacheFileName = "../app/cache/templates/".$form->getId().".js";

        if (!is_dir("../app/cache/templates")) {
            mkdir("../app/cache/templates");
        }

        if (file_exists( $cacheFileName ) && $utm_campaign == "") {
            $view = implode ("", file ($cacheFileName));
        } else {
            $view = $this->renderView(
                'TellawLeadsFactoryBundle::form-jquery.js.twig',
                array(
                    'formId' => $form->getCode(),
                    'formAction' => $this->container->get('router')->generate("_client_post_form", array(), true),
                    'trackingAction'=> $this->container->get('router')->generate("_client_form_tracking"),
                    'utm_campaign' => $utm_campaign, // Used for compatibility of old forms. Do not REMOVE
                    'form' => $form,
                    'post_url' => $post_url,
                    'hidden_tags' => $hidden_tags,
                ) );
            $fp = fopen($cacheFileName, 'w');
            fwrite($fp, $view);
            fclose($fp);

        }

        $response = new Response( $view );
		$response->headers->set('Content-Type', 'application/javascript');
		return $response;
	}

	/**
	 * @Route("/preview/twig/{code}", name="_client_twig_preview")
	 * @ParamConverter("form")
	 */
	public function getTwigFormPreview(Form $form)
	{
		return $this->render('TellawLeadsFactoryBundle:Front:display_twig_form.html.twig', array('form' => $form));
	}

    /**
     * @Route("/form/{id}", name="_client_get_form")
     * @ParamConverter("form")
     */
    public function getFormAction(Form $form)
    {
        $formUtils = $this->get("form_utils");
        $html = $formUtils->buildHtmlForm( $form );
        return $this->render(
            'TellawLeadsFactoryBundle:Front:display_form.html.twig',
            array(  'formHtmlObject' => $html )
        );

    }

    /**
     * @deprecated
     * @Route("/form/js/{code}/{utm_campaign}", name="_client_get_form_js")
     * @ParamConverter("form")
     */
    public function getFormAsJsAction(Form $form, $utm_campaign = '')
    {
        /** @var \Tellaw\LeadsFactoryBundle\Utils\JsUtils $formUtils */
        $formUtils = $this->get("js_utils");
        $jsForm = $formUtils->buildAndWrapForm ($form);

        // Track call request
        /** @var \Tellaw\LeadsFactoryBundle\Entity\Tracking $tracking */
        $tracking = new Tracking();
        if (trim($utm_campaign) == '') $utm_campaign =  $form->getUtmcampaign();
        $tracking->setUtmCampaign( $utm_campaign );
        $tracking->setForm( $form );
        $tracking->setCreatedAt( new \DateTime() );

        $em = $this->getDoctrine()->getManager();
        $em->persist($tracking);
        $em->flush();

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

            $formTypeObject = $this->get('leadsfactory.form_type_repository')->find((string)$request->get ("lfFormType"));
            $formId = $request->get("lfFormId");
            $logger->info ("Id FORM Posted : ".$formId);

            $formObject = $this->get('leadsfactory.form_repository')->find($formId);

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
	        if (array_key_exists('email', $fields)) {
		        $leads->setEmail($fields['email']);
	        }

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

				if($redirectUrlSuccess == 'redirect_url') {
					$logger->info('redirect url : '.$redirectUrlSuccess);
					$redirectUrlSuccess = $fields['redirect_url'];
				}

	            if(isset($config['redirect']['redirect_with_id']) && $config['redirect']['redirect_with_id'] == true){
		            if(strpos($redirectUrlSuccess, '?')){
			            $paramsSep = '&';
		            }else{
			            $paramsSep = '?';
		            }
		            $redirectUrlSuccess = $redirectUrlSuccess. $paramsSep . 'lead_id='.$leads->getId().'&key='.$formUtils->getApiKey($formObject);
	            }

	            return $this->redirect( $redirectUrlSuccess );
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
            ->setBody(
                $this->renderView(
                    'TellawLeadsFactoryBundle:'.$template,
                    array(
                        'fields' => $data,
                        'intro' => 'Nouvelle DI issue du formulaire '.$leads->getForm()->getName()
                    )
                ),
                'text/html'
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
     * @param string $template
     * @param array $data
     * @return mixed
     */
    protected function renderTemplate($template, $data)
    {
	    $data['template'] = $template;

	    $string = $this->renderView(
		    'TellawLeadsFactoryBundle::template_from_string.raw.twig',
		    $data
	    );

	    return $string;
    }
}

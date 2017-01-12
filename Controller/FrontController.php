<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\Tracking;
use Tellaw\LeadsFactoryBundle\Response\TransparentPixelResponse;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;

/**
 *
 */
class FrontController extends CoreController
{
    /**
     * Index Redirect
     * @Route("/", name="website_index")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('_monitoring_dashboard_forms', array(), 301);
    }

    /**
     * Tracking callback method
     *
     * Method used to generate a transparent pixel to track display of a form, including the UTM.
     *
     * @Route("/form/trck", name="_client_form_tracking")
     * @Route("/form/trck/{code}/")
     * @Route("/form/trck/{code}/{utm_campaign}")
     */
    public function trackingAction(Form $form, $utm_campaign = '')
    {

        // Track call request
        /** @var \Tellaw\LeadsFactoryBundle\Entity\Tracking $tracking */
        $tracking = new Tracking();
        if (trim($utm_campaign) == '') {
            $utm_campaign = $form->getUtmcampaign();
        }

        $tracking->setUtmCampaign($utm_campaign);
        $tracking->setForm($form);
        $tracking->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($tracking);
        $em->flush();

        return new TransparentPixelResponse();

    }


    /**
     *
     * Method used for FORM Post in Twig Format
     *
     * @Route("/form/twig/{code}/{utm_campaign}", name="_client_twig")
     * @ParamConverter("form")
     */
    public function twigAction(Form $form, $utm_campaign = '')
    {

        $post_url = $this->get('router')->generate('_client_post_form', array(), true);
        $hidden_tags = $this->get('form_utils')->getHiddenTags($form);
        $prefUtils = $this->get('preferences_utils');

        $cacheFileName = "../app/cache/templates/" . $form->getId() . ".js";

        if (!is_dir("../app/cache/templates")) {
            mkdir("../app/cache/templates");
        }

        // Get the correct path for the formAction
        $url = $this->container->get('router')->generate("_client_post_form", array(), true);

        $scope = $form->getScope();
        if (isset($scope)) {
            $scopeId = $scope->getId();
            $urlDb = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_URL', $scopeId);

            if (trim($urlDb) != "") {
                if (strstr($urlDb, "web/")) {
                    $url = $urlDb . "client/post";
                } else {
                    $url = $urlDb . "web/client/post";
                }
            }
        }

        if (file_exists($cacheFileName) && $utm_campaign == "") {
            $view = implode("", file($cacheFileName));
        } else {
            $view = $this->renderView(
                'TellawLeadsFactoryBundle::form-jquery.js.twig',
                array(
                    'formId' => $form->getCode(),
                    'formAction' => $url,
                    'trackingAction' => $this->container->get('router')->generate("_client_form_tracking"),
                    'utm_campaign' => $utm_campaign, // Used for compatibility of old forms. Do not REMOVE
                    'form' => $form,
                    'post_url' => $post_url,
                    'hidden_tags' => $hidden_tags,
                ));
            $fp = fopen($cacheFileName, 'w');
            fwrite($fp, $view);
            fclose($fp);

        }

        $response = new Response($view);
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * @Route("/form/{id}", name="_client_get_form")
     * @ParamConverter("form")
     */
    public function getFormAction(Form $form)
    {
        $formUtils = $this->get("form_utils");
        $html = $formUtils->buildHtmlForm($form);

        return $this->render(
            'TellawLeadsFactoryBundle:Front:display_form.html.twig',
            array('formHtmlObject' => $html)
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
        $jsForm = $formUtils->buildAndWrapForm($form);

        // Track call request
        /** @var \Tellaw\LeadsFactoryBundle\Entity\Tracking $tracking */
        $tracking = new Tracking();
        if (trim($utm_campaign) == '') $utm_campaign = $form->getUtmcampaign();
        $tracking->setUtmCampaign($utm_campaign);
        $tracking->setForm($form);
        $tracking->setCreatedAt(new \DateTime());

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
    public function postLeadsAction(Request $request)
    {

        $logger = $this->get('logger');
        $formUtils = $this->get("form_utils");
        $fields = $request->get("lffield");
        $exportUtils = $this->get('export_utils');
        $searchUtils = $this->get('search.utils');
        $referer = $this->getRequest()->headers->get('referer');


//         if ( !$formUtils->checkFormKey( $request->get("lfFormKey"), $request->get("lfFormId") ) )
//            throw new \Exception ("Form Key is not allowed");

        try {

            $formTypeObject = $this->get('leadsfactory.form_type_repository')->find((string)$request->get("lfFormType"));
            $formId = $request->get("lfFormId");
            $logger->info("Id FORM Posted : " . $formId);

            $formObject = $this->get('leadsfactory.form_repository')->find($formId);

            // Read configuration to map attributes correctly
            $config = $formObject->getConfig();

            $redirectUrlSuccess = isset($config['redirect']['url_success']) ? $config['redirect']['url_success'] : '';
            $redirectUrlError = isset($config['redirect']['url_error']) ? $config['redirect']['url_error'] : '';

            if (array_key_exists('configuration', $config)) {

                if (array_key_exists('lastname', $config["configuration"])) {
                    $fields["lastname"] = ucfirst($fields[$config["configuration"]["lastname"]]);
                }

                if (array_key_exists('firstname', $config["configuration"])) {
                    $fields["firstname"] = ucfirst($fields[$config["configuration"]["firstname"]]);
                }
            }

            // On vérifie s'il y a des fichiers uploadés
            $form_dir_path = $this->container->getParameter('kernel.root_dir') . '/../datas/';
            if (isset($config['upload_files']) && $config['upload_files'] == 'OK') {
                // On vérifie l'extension
                $all_files = $request->files->all();
                $fs = new Filesystem();
                $filesToSave = array();
                $i = 0;
                foreach ($all_files['lffield'] as $field_name => $file) {

                    // Fichier pesant 1Mo max
                    if (isset($file) && $file->getClientSize() != 0 && $file->getClientSize() <= 1048576) {
                        // On créé (s'il n'existe pas) un répertoire portant le nom de l'ID form Leads
                        if (!$fs->exists($form_dir_path . $formId)) {
                            $fs->mkdir($form_dir_path . $formId, 0760);
                        } else {
                            // Droits en écriture ?
                            if (!is_writable($form_dir_path . $formId)) {
                                $fs->chmod($form_dir_path . $formId, 0760);
                            }
                        }
                        // On récupère la liste des fichiers à uploader
                        $filesToSave[$i] = array();
                        $filesToSave[$i]['field_name'] = $field_name;
                        $filesToSave[$i]['file'] = $file;
                        $filesToSave[$i]['extension'] = $file->getClientOriginalExtension();
                        // On sauvegarde les noms originaux des fichiers
                        $logger->info("Field name : " . $field_name);
                        $fields[$field_name] = $file->getClientOriginalName();
                    }
                    $i++;
                }
            }

            $fields = $this->get('form_utils')->preProcessData($formId, $fields);
            $json = json_encode($fields);

            // Create new Leads Entity Object
            $leads = new Leads();
            $leads->setIpadress($this->get('request')->getClientIp());
            $leads->setUserAgent($this->get('request')->server->get("HTTP_USER_AGENT"));
            $leads->setFirstname(@$fields["firstname"]);
            $leads->setLastname(@$fields["lastname"]);
            $leads->setData($json);
            $leads->setLog("leads importée le : " . date('Y-m-d h:s'));
            $leads->setUtmcampaign(@$fields["utmcampaign"]);
            $leads->setFormType($formTypeObject);
            $leads->setForm($formObject);
            $leads->setTelephone(@$fields["phone"]);
            if (array_key_exists('email', $fields)) {
                $leads->setEmail($fields['email']);
            }

            // Assignation de la leads si la configuration est presente
            if (array_key_exists('configuration', $config)) {

                if (array_key_exists('assign', $config["configuration"])) {

                    $assign = trim($config["configuration"]["assign"]);
                    $user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail($assign);

                    if ($user != null) {
                        $leads->setUser($user);
                    } else {
                        $logger->info("Frontcontroller : Assign tu a User that does not exists! " . $assign);
                    }

                }

                if (array_key_exists('status', $config["configuration"])) {
                    $status = trim($config["configuration"]["status"]);
                    $leads->setWorkflowStatus($status);
                }

                if (array_key_exists('type', $config["configuration"])) {
                    $type = trim($config["configuration"]["type"]);
                    $leads->setWorkflowType($type);
                }

                if (array_key_exists('theme', $config["configuration"])) {
                    $theme = trim($config["configuration"]["theme"]);
                    $leads->setWorkflowTheme($theme);
                }

            }

            // Assignation de la leads si  l'information est contenue dans les données de la leads
            if (array_key_exists('lf-assign', $fields)) {

                $assign = trim($fields["lf-assign"]);
                $user = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findOneByEmail($assign);

                if ($user != null) {
                    $leads->setUser($user);
                } else {
                    $logger->info("Frontcontroller : Assign to a User that does not exists! " . $assign);
                }

            }

            if (array_key_exists('lf-status', $fields) && trim($fields["lf-status"] != "")) {
                $status = trim($fields["lf-status"]);
                $leads->setWorkflowStatus($status);
            }

            if (array_key_exists('lf-type', $fields) && trim($fields["lf-type"] != "")) {
                $type = trim($fields["lf-type"]);
                $leads->setWorkflowType($type);
            }

            if (array_key_exists('lf-theme', $fields) && trim($fields["lf-theme"] != "")) {
                $theme = trim($fields["lf-theme"]);
                $leads->setWorkflowTheme($theme);
            }

            $status = $exportUtils->hasScheduledExport($formObject->getConfig()) ? $exportUtils::$_EXPORT_NOT_PROCESSED : $exportUtils::$_EXPORT_NOT_SCHEDULED;
            $leads->setStatus($status);

            $leads->setCreatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($leads);
            $em->flush();

            // On déplace le(s) fichier(s) uploadé(s) vers le répertoire final
            if (isset($filesToSave) && !empty($filesToSave)) {
                foreach ($filesToSave as $file_item) {
                    $field_name = $file_item['field_name']; // Nom du champ dans la Lead
                    $file = $file_item['file']; // Objet permettant de gérer le fichier à uploader
                    $extension = $file_item['extension'];
                    $filename = $leads->getId() . '_' . $field_name . '.' . $extension;
                    $file->move($form_dir_path . $formId, $filename);
                    $logger->info("Fichier : " . $filename . " uploadés :)");
                }
            }

            // Create export job(s)
            if ($status == $exportUtils::$_EXPORT_NOT_PROCESSED) {
                $exportUtils->createJob($leads);
            }

            // Index leads on search engine
            $leads_array = $this->get('leadsfactory.leads_repository')->getLeadsArrayById($leads->getId());
            $searchUtils->indexLeadObject($leads_array, $leads->getForm()->getScope()->getCode());

            //Send notification
            if (isset($config['notification'])) {
                $this->sendNotification($config['notification'], $leads);
            }

            //Send confirmation email
            if (isset($config['confirmation_email'])) {
                $this->sendConfirmationEmail($config['confirmation_email'], $leads);
            }

            //Redirect to success page
            if (!empty($redirectUrlSuccess)) {

                if ($redirectUrlSuccess == 'redirect_url') {
                    $logger->info('redirect url : ' . $redirectUrlSuccess);
                    $redirectUrlSuccess = $fields['redirect_url'];
                }

                if (isset($config['redirect']['redirect_with_id']) && $config['redirect']['redirect_with_id'] == true) {
                    if (strpos($redirectUrlSuccess, '?')) {
                        $paramsSep = '&';
                    } else {
                        $paramsSep = '?';
                    }
                    $redirectUrlSuccess = $redirectUrlSuccess . $paramsSep . 'lead_id=' . $leads->getId() . '&key=' . $formUtils->getApiKey($formObject);
                }
                $logger->info("REDIRECT TO : ".$redirectUrlSuccess);
                return $this->redirect($redirectUrlSuccess);
            }

        } catch (\Exception $e) {
            $redirectUrlError = $referer;
            $logger->error('postLeadsAction Error '.$e->getMessage());
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
        $default = $request->query->get('default');

        if (empty($parentValue)) {
            $optionsHtml = '<option value="">' . $default . '</option>';

        } else {
            $parentList = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findByCode($parentCode);
            $parentItem = $this->getdoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->findOneBy(array(
                'value' => $parentValue,
                'referenceList' => $parentList
            ));

            $children = (!empty($parentItem)) ? $parentItem->getChildren()->getValues() : array();

            $optionsHtml = '<option value="">Sélectionnez</option>';
            foreach ($children as $child) {
                $optionsHtml .= '<option value="' . $child->getValue() . '">' . $child->getName() . '</option>';
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

        if (!isset($params['to'])) {
            $logger->error('No recipient available, check JSON form config');

            return;
        }

        $to = $params['to'];
        $from = isset($params['from']) ? $params['from'] : $exportUtils::NOTIFICATION_DEFAULT_FROM;
        $subject = isset($params['subject']) ? $params['subject'] : 'Nouvelle DI issue du formulaire ' . $leads->getForm()->getName();
        $template = isset($params['template']) ? $params['template'] : $exportUtils::NOTIFICATION_DEFAULT_TEMPLATE;

        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'TellawLeadsFactoryBundle:' . $template,
                    array(
                        'fields' => $data,
                        'intro' => 'Nouvelle DI issue du formulaire ' . $leads->getForm()->getName()
                    )
                ),
                'text/html'
            );

        try {
            $result = $this->get('mailer')->send($message);
        } catch (Exception $e) {
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

        if (empty($params['to']['email_input_id']) || empty($params['to']['firstname_input_id']) || empty($params['to']['lastname_input_id'])) {
            $logger->error('bad confirmation email configuration (form ' . $form->getName() . ')');

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

        try {
            $result = $this->get('mailer')->send($message);
        } catch (Exception $e) {
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

    /**
     * Preview of a TWIG FORM
     *
     * @Route("/preview/twig/{code}", name="_front_twig_preview")
     * @ParamConverter("form")
     */
    public function getTwigFormPreviewAction(Form $form)
    {
        return $this->render(
            'TellawLeadsFactoryBundle:Front:display_twig_form.html.twig',
            array('form' => $form)
        );
    }
}

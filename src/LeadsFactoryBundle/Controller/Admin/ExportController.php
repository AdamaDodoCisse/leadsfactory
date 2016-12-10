<?php

namespace LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use LeadsFactoryBundle\Entity;
use LeadsFactoryBundle\Shared\CoreController;
use LeadsFactoryBundle\Utils\ExportUtils;

/**
 * @Route("/entity")
 */
class ExportController extends CoreController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Start export
     *
     * @Route("/leads/export", name="_entity_leads_export")
     * @Secure(roles="ROLE_USER")
     */
    public function exportAction(Request $request)
    {
        if ($this->get("core_manager")->isDomainAccepted()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $formId = $request->query->get('id');
        $redirectUrl = $request->query->get('redirect_url') ? $request->query->get('redirect_url') : '_export_history';

        $logger = $this->get('export.logger');

        if (is_null($formId)) {
            $forms = $this->get('leadsfactory.form_repository')->findAll();
        } else {
            $forms = $this->get('leadsfactory.form_repository')->find($formId);
            $forms = array($forms);
        }

        foreach ($forms as $form) {
            try {
                $this->get('export_utils')->export($form);
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
            }
        }

        return $this->redirect($this->generateUrl($redirectUrl));
    }


    /**
     * Display export jobs
     *
     * @route("/export/history/{page}/{limit}/{keyword}", name="_export_history")
     * @Secure(roles="ROLE_USER")
     */
    public function showHistoryAction($page = 1, $limit = 25, $keyword = '')
    {
        $list = $this->getList('TellawLeadsFactoryBundle:Export', $page, $limit, $keyword, array('user' => $this->getUser(), 'statuses' => array()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Export:list.html.twig',
            array(
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

    /**
     * Display export jobs
     *
     * @route("/export/history-error/{page}/{limit}/{keyword}", name="_export_history_error")
     * @Secure(roles="ROLE_USER")
     */
    public function showHistoryErrorAction($page = 1, $limit = 25, $keyword = '')
    {

        $list = $this->getList('TellawLeadsFactoryBundle:Export', $page, $limit, $keyword, array('user' => $this->getUser(), 'statuses' => array(ExportUtils::$_EXPORT_ONE_TRY_ERROR, ExportUtils::$_EXPORT_MULTIPLE_ERROR)));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Export:list.html.twig',
            array(
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

    /**
     * Display export jobs
     *
     * @route("/export/history-emailnotvalidated/{page}/{limit}/{keyword}", name="_export_history_emailnotvalidated")
     * @Secure(roles="ROLE_USER")
     */
    public function showHistoryNotValidatedEmailAction($page = 1, $limit = 25, $keyword = '')
    {

        $list = $this->getList('TellawLeadsFactoryBundle:Export', $page, $limit, $keyword, array('user' => $this->getUser(), 'statuses' => array(ExportUtils::EXPORT_EMAIL_NOT_CONFIRMED)));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Export:list.html.twig',
            array(
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

}

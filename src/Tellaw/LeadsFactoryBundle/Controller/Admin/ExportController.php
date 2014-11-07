<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Utils\ExportUtils;
use Tellaw\LeadsFactoryBundle\Entity;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity")
 */
class ExportController extends AbstractEntityController
{

    /**
     * Start export
     *
     * @Route("/leads/export", name="_entity_leads_export")
     * @Secure(roles="ROLE_USER")
     */
    public function exportAction(Request $request)
    {
        $formId = $request->query->get('id');
        $redirectUrl = $request->query->get('redirect_url') ? $request->query->get('redirect_url') : '_export_history';

        $logger = $this->get('export.logger');

        if(is_null($formId)){
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        }else{
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($formId);
            $forms = array($forms);
        }

        foreach($forms as $form){
            try{
                $this->get('export_utils')->export($form);
            }catch(\Exception $e){
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
    public function showHistoryAction($page=1, $limit=10, $keyword='')
    {
        $list = $this->getList('TellawLeadsFactoryBundle:Export', $page, $limit, $keyword);

        return $this->render(
            $this->getBaseTheme().':entity/Export:list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }



}

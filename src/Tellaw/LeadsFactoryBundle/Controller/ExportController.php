<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

/**
 * @Route("/entity")
 */
class ExportController extends Controller
{

    /**
     * Start export
     *
     * @Route("/leads/export", name="_entity_leads_export")
     */
    public function exportAction(Request $request)
    {
        $formId = $request->query->get('id');
        $redirectUrl = $request->query->get('redirect_url') ? $request->query->get('redirect_url') : '_export_history';

        if(is_null($formId)){
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        }else{
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($formId);
        }

        foreach($forms as $form){
            $this->get('export_utils')->export($form);
        }
        return $this->redirect($this->generateUrl($redirectUrl));
    }


    /**
     * Display export jobs
     *
     * @route("/export/history", name="_export_history")
     */
    public function showHistoryAction()
    {
        $jobs = $this->get('doctrine')->getRepository('TellawLeadsFactoryBundle:Export')->findAll();
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Export:list.html.twig',
            array('jobs' => $jobs)
        );
    }



}

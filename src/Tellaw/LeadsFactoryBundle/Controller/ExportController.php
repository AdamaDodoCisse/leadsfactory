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
     * @Route("/export", name="_entity_export")
     */
    public function ExportAction(Request $request)
    {
        $formId = $request->query->get('id');
        $redirectUrl = $request->query->get('redirect_url') ? $request->query->get('redirect_url') : '_leads_list';

        if(is_null($formId)){
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();
        }else{
            $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($formId);
        }

        foreach($forms as $form){
            $configMethods = $form->getExportMethods();

            foreach($configMethods as $method => $config){

                if($this->get('export_utils')->isValidExportMethod($method)){
                    $leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->findBy(
                        array(
                            'status' => array(0, 2),
                            'form' => $form->getId())
                    );
                    $this->get($method.'_method')->export($leads, $form);
                }
            }
        }
        return $this->redirect($this->generateUrl($redirectUrl));
    }

    /**
     * @route("/export/history", name="_export_history")
     */
    public function showHistoryAction()
    {
        $history = $this->get('doctrine')->getRepository('TellawLeadsFactoryBundle:Export')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Export:list.html.twig',
            array('history' => $history)
        );
    }



}

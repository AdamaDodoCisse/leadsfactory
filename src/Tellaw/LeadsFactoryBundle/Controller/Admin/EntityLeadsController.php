<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\LeadsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityLeadsController extends AbstractLeadsController
{

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/leads/list/{page}", name="_leads_list")
     */
    public function indexAction($page=1)
    {

        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->findAll();

        $leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getList($page, 10);
        $total = count($leads);
        $pages_count = ceil($total/10);

        $pagination = array(
            'page'              => $page,
            'pages_count'       => $pages_count,
            'pagination_min'    => ($page>5) ? $page -5 : 1,
            'pagination_max'    => ($pages_count - $page) > 5 ? $page +5 : $pages_count,
            'route'             => '_leads_list'
        );

        return $this->render(
            $this->getBaseTheme().':entity/Leads:list.html.twig',
            array(
                'elements'      => $leads,
                'pagination'    => $pagination
            )
        );

    }

    /**
     * @Route("/leads/edit/{id}", name="_leads_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

        $type = new LeadsType();

        $form = $this->createForm(  $type,
                                    $formData,
                                    array(
                                        'method' => 'POST'
                                    )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            // fait quelque chose comme sauvegarder la tâche dans la bdd

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_leads_list'));
        }

        return $this->render($this->getBaseTheme().':entity/Leads:edit.html.twig', array(  'form' => $form->createView(),
                                                                                             'title' => "Edition d'un leads"));

    }

}
